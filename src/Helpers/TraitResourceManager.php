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

    public function getServiceRepo($code = '', $params = [])
    {
        return $this->getObject('service-repo', $code, $params);
    }

    public function getModelObj($code = '', $params = [])
    {
        return $this->getObject('model', $code, $params);
    }

    protected function getObject($type, $code, $params)
    {
        $code = !empty($code) ? $this->getAppcode() . '-' . $code : get_called_class();
        return $this->resource->getObject($type, $code, $params);
    }

    protected function getAppcode()
    {
        return 'passport';
    }
}
