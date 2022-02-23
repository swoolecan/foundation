<?php

namespace Swoolecan\Foundation\Services;

//use Illuminate\Support\Facades\Cache;
use EasyWeChat\Factory;

trait TraitWechatService
{
    protected function pointRepository()
    {
        return false;
    }

    public function getWechatServe($type, $code = '')
    {
        $code = empty($code) ? 'default' : $code;
        switch ($type) {
        case 'platform':
            return \EasyWeChat::openPlatform($code);
        case 'official':
			$config = config('wechat.official_account.' . $code);
			return Factory::officialAccount($config);
            //return \EasyWeChat::officialAccount($code);
        case 'work':
			$config = config('wechat.work.' . $code);
			return Factory::work($config);
        case 'miniProgram':
			$config = config('wechat.mini_program.' . $code);
			return Factory::work($config);
        }
        //$app   = Factory::openPlatform($config);
    }

    public function getAuthurl($code = '')
    {
        $code = empty($code) ? 'default' : $code;
        //$config = config('wechat.open_platform.' . $code);
		$config = config('wechat.official_account.' . $code);
		$baseUrl = 'https://open.weixin.qq.com/connect/qrconnect?';
		$params = [
			'appid' => $config['app_id'],
			'response_type' => 'code',
			'scope' => 'snsapi_login',
			'state' => 'pad',
			'redirect_uri' => $config['redirecturl'],
		];
		return $baseUrl . http_build_query($params);
        //$url = appid=wxe7bea13954476147&redirect_uri=https%3A%2F%2Fwww.liupinshuyuan.com%2Flogin_callback%2Fweixin%2FLogin%2Fno_register_display.html&response_type=code&scope=snsapi_login&state=22983018bb868830094f3ae90f701701#wechat_redirect';
        //$url = $this->getWechatServe('platform', $code)->getPreAuthorizationUrl($config['redirecturl']);
    }

	public function getOauthurl()
	{
        $app = $this->getWechatServe('official');
        $oauth = $app->oauth;
        $oauth->redirect()->send();
        exit();
	}

	public function initUserinfo($oauth)
	{
		//$test = '{"id":"ozjtT0Wl6yBS27BDrxYWeyoc3j4k","name":"wangcan","nickname":"wangcan","avatar":"http:\/\/thirdwx.qlogo.cn\/mmopen\/vi_32\/Q0j4TwGTfTJgyRhHgzZCiaV9cF6hII4S4MTcmhAJkLY0bLjNmOibtALq6NngISCwhiaCoxtnOlEMiclSgm4nxiaibia1w\/132","email":null,"original":{"openid":"ozjtT0Wl6yBS27BDrxYWeyoc3j4k","nickname":"wangcan","sex":1,"language":"zh_CN","city":"","province":"","country":"中国","headimgurl":"http:\/\/thirdwx.qlogo.cn\/mmopen\/vi_32\/Q0j4TwGTfTJgyRhHgzZCiaV9cF6hII4S4MTcmhAJkLY0bLjNmOibtALq6NngISCwhiaCoxtnOlEMiclSgm4nxiaibia1w\/132","privilege":[],"unionid":"owQ_-08mXi5_5dFTtN4Z95otsngU"},"token":"33_DRXqJVQzgij4kCMlRLNtu6_Od170TpZYVaPtu9ijMOSH4Oz7syQajqS2r3614ZLDHNE0ApIm2xlar9Vv2JG_cWLSitwfQAeWF1Iblk0LF5s","provider":"WeChat"}';
		//$user = json_decode($test, true);
		$user = $oauth->user();
		$user = $user->toArray();
		//$user = $user->toJSON();
        return $this->formatUserData($user);
	}

    public function formatUserData($user)
    {
        $data = [
            //'data' => serialize($user),
        ];
        $original = [];
        if (isset($user['original'])) {
            $original = $user['original'];
            unset($user['original']);
        }
        $user = array_merge($user, $original);

        $pairs = [
            'openid', 'name', 'nickname',
            'headimgurl', 'token', 'unionid',
            'sex', 'language', 'city', 'province', 'country',
        ];
        foreach ($pairs as $key) {
            $value = isset($user[$key]) ? $user[$key] : '';
            if ($key == 'headimgurl' && empty($value) && !empty($user['avatar'])) {
                $value = $user['avatar'];
            }
            $value = empty($value) && $key == 'sex' ? 0 : $value;
            $data[$key] = $value;
        }
        return $data;
    }

    public function getWechatUser($wechat = null)
    {
        $wechat = is_null($wechat) ? $this->getWechatServe('official') : $wechat;
        $oauth = $wechat->oauth;
        try {
            //$test = '{"id":"ozjtT0Wl6yBS27BDrxYWeyoc3j4k","name":"wangcan","nickname":"wangcan","avatar":"http:\/\/thirdwx.qlogo.cn\/mmopen\/vi_32\/Q0j4TwGTfTJgyRhHgzZCiaV9cF6hII4S4MTcmhAJkLY0bLjNmOibtALq6NngISCwhiaCoxtnOlEMiclSgm4nxiaibia1w\/132","email":null,"original":{"openid":"ozjtT0Wl6yBS27BDrxYWeyoc3j4k","nickname":"wangcan","sex":1,"language":"zh_CN","city":"","province":"","country":"中国","headimgurl":"http:\/\/thirdwx.qlogo.cn\/mmopen\/vi_32\/Q0j4TwGTfTJgyRhHgzZCiaV9cF6hII4S4MTcmhAJkLY0bLjNmOibtALq6NngISCwhiaCoxtnOlEMiclSgm4nxiaibia1w\/132","privilege":[],"unionid":"owQ_-08mXi5_5dFTtN4Z95otsngU"},"token":"33_DRXqJVQzgij4kCMlRLNtu6_Od170TpZYVaPtu9ijMOSH4Oz7syQajqS2r3614ZLDHNE0ApIm2xlar9Vv2JG_cWLSitwfQAeWF1Iblk0LF5s","provider":"WeChat"}';
            //$user = json_decode($test, true);
            $user = $oauth->user();
            $user = $user->toArray();
        } catch (\Exception $e) {
            return ['code' => 400, 'message' => '获取微信用户信息失败'];
        }
        //$user = $user->toJSON(); 
        return $this->formatUserData($user);
    }

    public function sendTemplateMessage($wechat, $data)
    {
        $templateMessage = $wechat->template_message;
        $r = $templateMessage->send($data);
        \Log::debug(serialize($r));
        \Log::debug(serialize($data));
        return true;
    }

    public function publishMenu()
    {
        $buttons = [
            [
                'name' => '乐写字',
                'sub_button' => [
                    [
                        'type' => 'view',
                        'name' => 'APP使用手册',
                        'url'  => 'https://smartpen.liupinshuyuan.com/faq.html?id=4',
                    ],
                ],
            ],
            [
                'name' => '课程',
                'sub_button' => [
                    [
                        'type' => 'view',
                        'name' => '课程激活',
                        'url'  => 'https://smartpen.liupinshuyuan.com/course_active.html',
                    ],
                    [
                        'type' => 'view',
                        'name' => '购买课程',
                        'url' => 'https://www.liupinshuyuan.com/sale/277.html?sale=M83OG=iU1e-G3eVnAWenIeNLIg6ideGwA',
                        //'url'  => 'https://www.liupinshuyuan.com/sale/273.html?sale=gKNZ-sLo=C2utqxHLoxQcoJX2IceZpUP3',
                    ],
                ],
            ],
            [
                'name' => '软件下载',
                'type' => 'view',
                'url' => 'https://lxzdw.liupinshuyuan.com',
            ],
        ];
        print_R($buttons);
        $wechat = $this->getWechatServe('official');
        $r = $wechat->menu->create($buttons);
        var_dump($r);
    }

    /*public function sendDingNotice($title, $content, $url)
    {
        $ding = new \DingNotice\DingTalk(config('ding'));
        $r = $ding->link($title, $content, $url);
        \Log::debug(serialize($r));
        return true;
    }

    public function getQrcodeUrl($text, $ttl = 600)
    {
        $app = app('wechat.official_account');
        $result = Cache::remember("qrcode_{$text}", $ttl, function() use($app, $ttl, $text){
            return $app->qrcode->temporary($text, $ttl);
        });
        return $result ? $app->qrcode->url($result['ticket']) : '';
    }*/
}
