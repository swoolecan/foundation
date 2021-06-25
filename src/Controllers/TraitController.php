<?php

declare(strict_types = 1);

namespace Swoolecan\Foundation\Controllers;

use Swoolecan\Foundation\Helpers\TraitResourceManager;

trait TraitController
{
    use OperationTrait;
    use TraitResourceManager;

    public function getPointRequest($scene = '', $repository = null, $code = '')
    {
        //$type = empty($action) ? 'request' : 'request-' . $action;
        $code = !empty($code) ? $code : get_called_class();
        //$request = $this->resource->getObject($type, $code, false);
        $request = $this->getRequestObj(null, ['scene' => $scene, 'repository' => $repository]);
        //$request->setScene($scene);
        if (empty($request)) {
            return $this->request;
        }
        /*if ($repository) {
            $request->setRepository($repository);
        }*/

        if (method_exists($request, 'validateResolved')) {
            $request->validateResolved();
        }
        return $request;
    }

    public function dealCriteria($scene, $repository, $params)
    {
        return $repository->getDealSearchFields($scene, $params);
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
