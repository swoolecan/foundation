<?php
declare(strict_types = 1);

namespace Swoolecan\Foundation\Services;

use Overtrue\EasySms\EasySms;

trait TraitEasysmsService
{
    protected $createInfo;

    protected $returnInfo;
    protected $noRepository = true;

    public function __construct()
    {
    }

    public function getConfig($key = null, $subKey = null)
    {
        static $config;
        if (empty($config)) {
            $config = $this->config->get('easysms');
        }
        if (empty($key)) {
            return $config;
        }
        if (is_null($subKey)) {
            return $config[$key];
        }
        return $config[$key][$subKey];
    }

    /**
     * 发送短信
     *
     * @param string $mobile  对方手机号码
     * @param string $content 短信内容
     * @return boolean        短信是否发送成功
     */
    public function send($mobile, $templateCode, $data)
    {
        $template = $this->getTemplateData($templateCode);
        return $this->_send($mobile, $template, $data);
    }

    protected function _send($mobile, $template, $data)
    {
        if ($this->getConfig('isTest')) {
            return ['code' => 200, 'message' => 'success1'];
        }

		$content = $this->formatContent($template, $data);
        try {
            $easySms = new EasySms($this->getConfig());
            $easySms->send($mobile, $content);
        } catch (\Exception $e) {
            return $this->resource->throwException(400, '短信发送失败，请稍后重新操作0' . $e->getMessage());
        }
        return ['code' => 200, 'message' => 'success'];
    }

	public function sendCode($data)
	{
        $template = $this->getTemplateData($data['template']);

        $mobile = $data['mobile'];
        $type = $data['type'];
        $typeConfig = $this->getConfig('verifyCode', $type);
        if (empty($typeConfig)) {
            return $this->resource->throwException(400, "验证码类型{$type}有误");
        }
        $infoExist = $this->getCodeInfo($mobile . '-' . $type);
        $check = $this->checkSend($infoExist, $typeConfig);
        if ($check !== true) {
            return $check;
        }

        $param = [
            //'mobile' => $mobile, 
            'type' => $type, 
            'infoExist' => $infoExist,
            'typeConfig' => $typeConfig,
        ];
        $this->createInfo = $this->_createCode($param);
        $this->cacheCode($mobile . '-' . $type);
        return $this->_send($data['mobile'], $template, ['code' => $this->createInfo['code']]);
    }

	public function validateCode($data)
	{
        $type = $data['type'];
        $typeConfig = $this->getConfig('verifyCode', $type);
        $info = $this->getCodeInfo($data['mobile'] . '-' . $type);

		if (empty($info)) {
			return $this->resource->throwException(400, '没有向该手机号发送验证码，请重新操作');
		}

		if ($info['code'] != $data['code']) {
			$message = $this->getConfig('isTest') ? 'codeError-' . $info['code'] : '验证码错误';
			return $this->resource->throwException(400, $message);
		}

        $expire = isset($typeConfig['expire']) ? $typeConfig['expire'] : 300;
        if (time() > $info['updatedAt'] + $expire) {
			return $this->resource->throwException(400, '您的验证码已经过期，请重新获取');
        }
        return ['code' => 200, 'message' => 'success'];
	}

    protected function _createCode($param)
    {
        $typeConfig = $param['typeConfig'];
        $infoExist = $param['infoExist'];
        $length = isset($typeConfig['length']) ? $typeConfig['length'] : 4;
        $length = is_array($length) ? mt_rand($length[0], $length[1]) : $length;

        $code = '';
        for($i = 0; $i < $length; $i++) {
            $code .= $this->getConfig('isTest') ? 1 : mt_rand(0, 9);
        }

        if (!empty($infoExist) && date('Ymd', $infoExist['createdAt']) != date('Ymd', time())) {
            $infoExist['createdAt'] = time();
            //$infoExist['count'] = 0;
        }

        return [
            'code' => $code,
            'createdAt' => isset($infoExist['createdAt']) ? $infoExist['createdAt'] : time(),
            'updatedAt' => time(),
            'sendTimes' => isset($infoExist['sendTimes']) ? $infoExist['sendTimes'] + 1 : 1,
            //'count' => 0,
        ];
    }

    protected function checkSend($info, $typeConfig)
    {
        if (empty($info)) {
			return true;
        }

        $sendTimes = isset($typeConfig['sendTimes']) ? $typeConfig['sendTimes'] : 5;
        if ($info['sendTimes'] > $sendTimes) {
            return $this->resource->throwException(400, '您今天获取验证的次数已达到上限，请您暂停再操作');
        }

        $sleep = isset($typeConfig['sleep']) ? $typeConfig['sleep'] : 60;
        $diff = time() - $info['updatedAt'];
        if ($diff < $sleep) {
            $remain = $sleep - $diff;
            return $this->resource->throwException(400, "请您{$remain}秒后，再获取验证码");
        }

        return true;
	}

    protected function getTemplateData($templateCode)
    {
        static $templates;
        if (is_null($templates)) {
            $templates = $this->config->get('sms');
        }
        if (!isset($templates[$templateCode])) {
            return $this->resource->throwException(400, "短信模板{$templateCode}有误");
        }
        $template = $templates[$templateCode];
        $template['templateCode'] = $templateCode;

        return $template;
    }

    protected function formatContent($template, $data)
    {
        foreach ($data as $key => $value) {
            $placeholder = '{{' . strtoupper($key) . '}}';
            $content = str_replace($placeholder, $value, $template['content']);
        }
        $result = [
            'content'  => $content,
            'template' => $template['templateCode'],
            'data' => $data,
        ];
        return $result;
    }
}
