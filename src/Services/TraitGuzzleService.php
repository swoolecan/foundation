<?php

namespace Swoolecan\Foundation\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ConnectException;

trait TraitGuzzleService
{
    public function fetchRemoteData($requestParam, $data = [], $returnData = null)
    {
        //$rCode = $service->fetchRemoteData(['pointUrl' => $info['url']], [], ['callback' => 'responseStatus']);
        $defaultHeaders = [
            //'Accept' => 'application/json',
            //'Content-Type' => 'application/x-www-form-urlencoded'
            //'Content-Type' => 'application/json',
        ];
        $headers = isset($requestParam['headers']) ? array_merge($defaultHeaders, $requestParam['headers']) : $defaultHeaders;
        $client = new Client([
            'verify' => false, // 忽略SSL错误
            'headers' => $headers,
        ]);
        $method = $requestParam['method'] ?? 'get';
        $url = $this->formatUrl($requestParam);
        switch ($method) {
        case 'post':
            $paramType = $requestParam['paramType'] ?? 'form_params'; // json;
            $response = $client->post($url, [$paramType => $data]);
            //$response = $client->post($url, ['body' => json_encode($data)]); 
            break;
        case 'get':
            $url .= strpos($url, '?') !== false ? '&' . http_build_query($data) : '?' . http_build_query($data);
            try {
            $response = $client->request('GET', $url, ['timeout' => 1.5]);
            } catch (\Exception $e) {
                return '111';
            }
            break;
        }
        $callback = $returnData['callback'] ?? 'resultCallback';
        return $this->$callback($response, $returnData, $url, $data);
    }

    protected function responseStatus($response, $returnData, $url, $data)
    {
        return $response->getStatusCode();
    }

    protected function resultCallback($result, $url, $data, $returnData)
    {
        $body = $response->getBody(); //获取响应体，对象
        $bodyStr = (string)$body; //对象转字串
        $result = json_decode($bodyStr, true);
        $return = [];
        $returnResult = $returnData['returnResult'] ?? false;
        if (empty($result)) {
            \Log::debug('noresult----' . $url . '===' . serialize($data) . '--' . $bodyStr);
            return $returnData ? $return : ['status' => 1, 'msg' => 'error request!' . $url, 'data' => $data];
        }
        if (!isset($result['status'])) {
            \Log::debug('nostatus----' . $url . '===' . serialize($data) . '---' . $bodyStr);
            return $returnData ? $return : ['status' => 1, 'msg' => 'error request!' . $bodyStr, 'data' => $data];
        }
        if ($result['status'] == 0) {
            \Log::debug('status---1---' . $url . '===' . serialize($result) . '---' . serialize($data) . '--' . $bodyStr);
            return $returnData ? $return : $result;
        }
        //\Log::debug('status---0---' . $url . '===' . serialize($result) . '---' . serialize($data) . '--' . $bodyStr);
        return $result['data'] ?? [];
    }

    protected function formatUrl($requestParam)
    {
        if (isset($requestParam['pointUrl'])) {
            return $requestParam['pointUrl'];
        }
        $urlPre = $requestParam['urlPre'] ?? 'defaultUrlPre';

        $url = config('app.remote_url_' . $urlPre);
        if (isset($requestParam['path'])) {
            return rtrim($url, '/') . '/' . ltrim($path, '/');
        }
        return $url;
    }
}
