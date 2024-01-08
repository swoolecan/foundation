<?php

declare(strict_types = 1);

namespace Swoolecan\Foundation\Helpers;

trait TraitResourceManager
{
    public function getRepositoryObj($code = '', $params = [])
    {
        return $this->getObject('repository', $code, $params);
    }

    public function getServiceObj($code = '', $params = [])
    {
        return $this->getObject('service', $code, $params);
    }

    public function getRequestObj($code = '', $params = [])
    {
        return $this->getObject('request', $code, $params);
    }

    public function getServiceRepo($code = '', $params = [])
    {
        return $this->getObject('service-repo', $code, $params);
    }

    public function getModelObj($code = '', $params = [])
    {
        return $this->getObject('model', $code, $params);
    }

    public function getResourceObj($info, $scene, $code = '', $simpleResult = false)
    {
        $params = [
            'resource' => $info,
            'scene' => $scene,
            //'repository' => $repository,
            'simpleResult' => $simpleResult,
        ];
        return $this->getObject('resource', $code, $params);
    }

    public function getCollectionObj($infos, $scene, $code = '', $simpleResult = false)
    {
        $params = [
            'resource' => $infos,
            'scene' => $scene,
            //'repository' => $repository,
            'simpleResult' => $simpleResult
        ];
        return $this->getObject('collection', $code, $params);
    }

    protected function getObject($type, $code, $params)
    {
        if (!empty($code)) {
            if (strpos($code, '\\')) {
                return $code;
            }
            $module = $this->getAppcode();
            //$code = strpos($code, $module) === 0 ? $code : $module . '-' . $code;
            $code = strpos($code, '-') !== false ? $code : $module . '-' . $code;
        } else {
            $code = get_called_class();
        }
        $resource = method_exists($this, 'getResource') ? $this->getResource() : $this->resource;
        return $resource->getObject($type, $code, $params);
    }

    protected function getAppcode()
    {
        return 'passport';
    }

    public function createSingleOrderid($pre = '')
    {
        $yCode = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J'];
        $time = strval(time());
        $microtime = strval(microtime());
        $orderSn = $yCode[intval(date('Y')) - 2023] . strtoupper(date('m')) . date('d') . substr($time, -5) . substr($microtime, 2, 5) . sprintf('%02d', rand(0, 99));
        return $pre . $orderSn;
    }

    public function getVersion()
    {
        return request()->header('version');
    }

    public function compareVersion($version, $operation = '<=')
    {
        $compare = version_compare(strval($this->getVersion()), $version, $operation);
        return $compare;
    }

    public function compareBackendVersion($version, $operation = '<=')
    {
        $currentVersion = strval(currentBackendVersion());
        $compare = version_compare($currentVersion, $version, $operation);
        return $compare;
    }

    public function createRandomCode()
    {
        $characters = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        // 生成随机字母序列
        $randomString = str_shuffle($characters);
        // 例如，生成一个包含 10 个随机字母的序列
        $randomString = substr($randomString, 0, 10);
        return $randomString;
    }

    public function logTimestamp($start, $title)
    {
        $end = microtime(true);
        \Log::info('record-time-' . $title . '==' . ($end - $start));
    }

    public function getCurrentBackendRoles()
    {
        $request = request();
        $rolePermissions = $request->get('rolePermissions');
        return $rolePermissions['roles'] ?? [];
    }
}
