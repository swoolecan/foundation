<?php

namespace Swoolecan\Foundation\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ConnectException;

trait TraitGuzzleService
{
    /**
     * params 格式 ['headers' => [], 'paramType' => 'json|form_params', 'query' => [], 'data' => []]
     */
    public function fetchRemoteData($url, $method, $params, $returnData = [])
    {
        $headers = $defaultHeaders = [
            //'Accept' => 'application/json',
            //'Content-Type' => 'application/x-www-form-urlencoded'
            'Content-Type' => 'application/json',
        ];
        if (isset($params['headers'])) {
            $headers = array_merge($defaultHeaders, $params['headers']);
            unset($params['headers']);
        }
        $client = new Client([
            'verify' => false, // 忽略SSL错误
            'headers' => $headers,
        ]);

        if (isset($params['query'])) {
            $url .= strpos($url, '?') !== false ? '&' : '?';
            $url .= http_build_query($params['query']);
            unset($params['query']);
        }

        $paramType = $params['paramType'] ?? 'form_params'; // json;
        try {
            switch ($method) {
            case 'post':
                $response = $client->post($url, [$paramType => $params['data']]);
                //$response = $client->post($url, ['body' => json_encode($data)]); 
                break;
            case 'put':
                $response = $client->request('PUT', $url, [$paramType => $data]);
                break;
            case 'get':
                $url .= strpos($url, '?') !== false ? '&' : '?';
                $url .= http_build_query($params['data']);
                $response = $client->request('GET', $url, ['timeout' => 8.5]);
                break;
            }
        } catch (\Exception $e) {
            $code = $e->getCode();
            \Log::debug('guzzle-exception-' . $e->getMessage());
            if (!in_array($code, [422, 201])) {
                return ['pointReturnCode' => $e->getCode(), 'message' => '服务器异常'];
            }
            //echo $e->getMessage();
            $response = $e->getResponse();
        }
        return $this->dealResponse($response, $returnData, $url, $params);
    }

    /**
     * returnData = ['pointCallback' => '']
     */
    protected function dealResponse($response, $returnData, $url, $data)
    {
        $pointCallback = $returnData['callback'] ?? '';
        if (!empty($pointCallback)) {
            return $this->$pointCallback($response, $returnData, $url, $data);
        }

        $body = $response->getBody(); //获取响应体，对象
        $bodyStr = (string)$body; //对象转字串
        $result = json_decode($bodyStr, true);

        $code = $response->getStatusCode();
        if ($code != '200') {
            \Log::debug('noresult----' . $code . '=' . $url . '===' . serialize($data) . '--' . $bodyStr);
        }

        /*$code = $response->getStatusCode();
        $result['pointReturnCode'] = $code;
        $result['bodyStr'] = $bodyStr;*/
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

    protected function responseStatus($response, $returnData, $url, $data)
    {
        return $response->getStatusCode();
    }
}
