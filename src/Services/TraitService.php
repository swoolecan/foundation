<?php

declare(strict_types = 1);

namespace Swoolecan\Foundation\Services;

trait TraitService
{
    public function init()
    {
        if (!empty($this->pointRepository())) {
            $this->repository = $this->resource->getObject('repository', $this->pointRepository());
        }
    }

    protected function pointRepository()
    {
        return get_called_class();
    }

    public function __call($name, $arguments)
    {   
        return $this->repository->{$name}(...$arguments);
    }

    public function getRepositoryObj($code = '', $params = [])
    {
        return $this->getObject('repository', $code, $params);
    }

    protected function getObject($type, $code, $params)
    {
        $code = !empty($code) ? $this->getAppcode() . '-' . $code : get_called_class();
        return $this->resource->getObject($type, $code, $params);
    }
}
