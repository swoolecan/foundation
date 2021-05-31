<?php

namespace App\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ConnectException;

trait TraitGuzzleService
{
    public function getSysprebak($params)
    {
		$client = new Client(['verify' => false]); // 忽略SSL错误
        $url = env('SYSPRE_URL');
        $url .= '?' . http_build_query($params);
        $response = $client->request('GET', $url);
        var_dump($response);exit();
    }
    
    public function getSyspre($params)
    { 
        $url = env('SYSPRE_URL');
        $url .= '?' . http_build_query($params);
        return $this->curl($url);
    }

    public static function getTeachers()
    {
        static $teachers = null;
        if (!is_null($teachers)) {
            return $teachers;
        }
        $guzzle = new GuzzleServe();
        $datas = $guzzle->pushCommentCenter('post', '/server/teacher/list', ['per_page' => 200], true);
        $datas = empty($datas) || !isset($datas['data']) ? [] : $datas['data'];
        $teachers = [];
        foreach ($datas as $data) {
            $data['id'] = $data['back_user_id'];
            $teachers[$data['id']] = $data;
        }

        return $teachers;
    }

    public function pushCommentCenter($method, $path, $data, $returnData = false)
    {
        $headers = [
            //'Accept' => 'application/json',
            //'Content-Type' => 'application/x-www-form-urlencoded'
            //'Content-Type' => 'application/json',
        ];
        $client = new Client([
            'verify' => false, // 忽略SSL错误
            'headers' => $headers,
        ]);
        $data['plat_id'] = 3;
        $url = $this->getCommentCenterUrl($path);
        switch ($method) {
        case 'post':
            //echo $url;print_R($data);exit();
            $response = $client->post($url, ['form_params' => $data]); 
            break;
        case 'get':
            $url .= '?' . http_build_query($data);
            //echo $url;exit();
            $response = $client->request('GET', $url);
            break;
        }
        return $this->formatResult($response, $url, $data, $returnData);
    }

    protected function getCommentCenterUrl($path = '')
    {
        $url = config('app.comment_center_url');
        return rtrim($url, '/') . '/' . ltrim($path, '/');
    }

    protected function getCommentCenterMqUrl()
    {
        return config('app.comment_center_url_mq');
    }

    protected function formatResult($response, $url, $data, $returnData)
    {
        $body = $response->getBody(); //获取响应体，对象
        $bodyStr = (string)$body; //对象转字串
        $result = json_decode($bodyStr, true);

        $return = [];
        if (empty($result)) {
            \Log::debug('noresult----' . $url . '===' . serialize($data) . '--' . $bodyStr);
            return $returnData ? $return : ['status' => 1, 'msg' => 'error request!', 'data' => $data];
        }
        if (!isset($result['status'])) {
            \Log::debug('nostatus----' . $url . '===' . serialize($data) . '---' . $bodyStr);
            return $returnData ? $return : ['status' => 1, 'msg' => 'error request!' . $bodyStr, 'data' => $data];
        }
        if ($result['status'] == 1) {
            \Log::debug('status---1---' . $url . '===' . serialize($result) . '---' . serialize($data) . '--' . $bodyStr);
            return $returnData ? $return : $result;
        }
        \Log::debug('status---0---' . $url . '===' . serialize($result) . '---' . serialize($data) . '--' . $bodyStr);
        return $result['data'] ?? [];
    }

    public function pushCommentCenterMq($method, $path, $data, $returnData = false)
    {
        $headers = [
            'Accept' => 'application/json',
            'System' => 'commentMiddle',
            'Content-Type' => 'application/json',
        ];
        print_r($headers);
        $client = new Client([
            'verify' => false, // 忽略SSL错误
            'headers' => $headers,
        ]);
        $data['plat_id'] = 3;
        $data['syncUrl'] = $this->getCommentCenterUrl($path);
        $data['syncType'] = $method == 'get' ? 1 : 2;
        $url = $this->getCommentCenterMqUrl();
        //$response = $client->post($url, ['form_params' => $data]); 
        $response = $client->post($url, ['body' => json_encode($data)]); 
        return $this->formatResult($response, $url, $data, $returnData);
    }

    public function pushMq($method, $path, $data, $returnData = false)
    {
        $headers = [
            'Accept' => 'application/json',
            'System' => 'commentMiddle',
            'Content-Type' => 'application/json',
        ];
        $client = new Client([
            'verify' => false, // 忽略SSL错误
            'headers' => $headers,
        ]);
        $url = $this->getMqUrl($path);
        echo $url;
        $response = $client->post($url, ['body' => json_encode($data)]); 
        var_dump($response);
        return $this->formatResult($response, $url, $data, $returnData);
    }

    protected function getMqUrl($path = '')
    {
        $url = config('app.mq_url');
        return rtrim($url, '/') . '/' . ltrim($path, '/');
    }

    /**
     * @param $url 请求网址
     * @param bool $params 请求参数
     * @param int $ispost 请求方式
     * @param int $https https协议
     * @return bool|mixed
     */
    public static function curl($url, $params = false, $ispost = 0, $https = 0)
    {
        $httpInfo = array();
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/41.0.2272.118 Safari/537.36');
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        if ($https) {
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE); // 对认证证书来源的检查
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE); // 从证书中检查SSL加密算法是否存在
        }
        if ($ispost) {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
            curl_setopt($ch, CURLOPT_URL, $url);
        } else {
            if ($params) {
                if (is_array($params)) {
                    $params = http_build_query($params);
                }
                curl_setopt($ch, CURLOPT_URL, $url . '?' . $params);
            } else {
                curl_setopt($ch, CURLOPT_URL, $url);
            }
        }

        $response = curl_exec($ch);

        if ($response === FALSE) {
            echo "cURL Error: " . curl_error($ch);
            return false;
        }
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $httpInfo = array_merge($httpInfo, curl_getinfo($ch));
        curl_close($ch);
        return $response;
    }
}
