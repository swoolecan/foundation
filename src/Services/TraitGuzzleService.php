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
            try {
                $response = $client->post($url, [$paramType => $data]);
            } catch (\Exception $e) {
                $code = $e->getCode();
                \Log::debug('guzzle-exception-' . $e->getMessage());
                if (!in_array($code, [422, 201])) {
                    return ['pointReturnCode' => $e->getCode(), 'message' => '服务器异常'];
                }
                //echo $e->getMessage();
                $response = $e->getResponse();
            }
            //$response = $client->post($url, ['body' => json_encode($data)]); 
            break;
        case 'put':
            $paramType = $requestParam['paramType'] ?? 'form_params'; // json;
            try {
                $response = $client->request('PUT', $url, [$paramType => $data]);
            } catch (\Exception $e) {
                $response = $e->getResponse();
            }
            break;
        case 'get':
            //print_r($requestParam);
            $url .= strpos($url, '?') !== false ? '&' . http_build_query($data) : '?' . http_build_query($data);
            try {
                $response = $client->request('GET', $url, ['timeout' => 8.5]);
            } catch (\Exception $e) {
                //echo $e->getMessage();
                \Log::debug('guzzle-exception-' . $e->getMessage());
                $code = $e->getCode();
                if (!in_array($code, [422, 201])) {
                    return ['pointReturnCode' => $e->getCode(), 'message' => '服务器异常' . $e->getMessage()];
                }
                $response = $e->getResponse();
                //return ['pointReturnCode' => $e->getCode(), 'message' => $e->getMessage()];
            }
            break;
        }

        $callback = $returnData['callback'] ?? 'resultCallback';
        return $this->$callback($response, $returnData, $url, $data);
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

    protected function responseStatus($response, $returnData, $url, $data)
    {
        return $response->getStatusCode();
    }

    protected function resultCallback($response, $returnData, $url, $data)
    {
        $body = $response->getBody(); //获取响应体，对象
        $bodyStr = (string)$body; //对象转字串
        $result = json_decode($bodyStr, true);

        $code = $response->getStatusCode();
        if ($code != '200') {
            \Log::debug('noresult----' . $code . '=' . $url . '===' . serialize($data) . '--' . $bodyStr);
        }
        $code = $response->getStatusCode();
        $result['pointReturnCode'] = $code;
        $result['bodyStr'] = $bodyStr;
        return $result;
    }

    protected function centerCallback($response, $returnData, $url, $data)
    {
        $body = $response->getBody(); //获取响应体，对象
        $bodyStr = (string)$body; //对象转字串

        $code = $response->getStatusCode();
        $result['pointReturnCode'] = $code;
        $result['result'] = $bodyStr;
        return $result;
    }
}
