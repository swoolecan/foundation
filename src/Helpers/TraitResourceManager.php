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
}
