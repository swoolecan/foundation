<?php

declare(strict_types = 1);

namespace Swoolecan\Foundation\Controllers;

trait TraitController
{
    use OperationTrait;

    public function getRequestObj($scene = '', $repository = null, $code = '')
    {
        //$type = empty($action) ? 'request' : 'request-' . $action;
        $code = !empty($code) ? $code : get_called_class();
        //$request = $this->resource->getObject($type, $code, false);
        $request = $this->resource->getObject('request', $code, false);
        if (empty($request)) {
            return $this->request;
        }
        if ($repository) {
            $request->setRepository($repository);
        }
        $request->setScene($scene);

        if (method_exists($request, 'validateResolved')) {
            $request->validateResolved();
        }
        return $request;
    }

    public function dealCriteria($scene, $repository, $params)
    {
        return $repository->getDealSearchFields($scene, $params);
    }

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

    protected function getObject($type, $code, $params)
    {
        $code = !empty($code) ? $this->getAppcode() . '-' . $code : get_called_class();
        return $this->resource->getObject($type, $code, $params);
    }

    public function getVersion()
    {
        return $this->request->header('version');
    }

    public function getRouteParam($param)
    {
        return $this->resource->getRouteParam($param);
    }

    protected function getAppcode()
    {
        return '';
    }
}
