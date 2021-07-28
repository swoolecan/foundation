<?php

declare(strict_types = 1);

namespace Swoolecan\Foundation\Repositories;

trait CacheRpcTrait
{
    public function getRpcData($app, $resource, $key, $keyField = 'id')
    {
        $app = ucfirst($app);
        $class = "\Framework\Baseapp\RpcClient\\{$app}RpcClient";
        $client = $this->resource->getObjectByClass($class);
        return $client->getRpcData($app, $resource, $key, $keyField);
    }

    public function getRpcDatas($app, $resource, $key, $keyField = 'id')
    {
        $app = ucfirst($app);
        $class = "\Framework\Baseapp\RpcClient\\{$app}RpcClient";
        $client = $this->resource->getObjectByClass($class);
        return $client->getRpcData($app, $resource, $key, $keyField);
    }

    /*public function getCacheDatas($resource = null, $type = 'list', $simple = true, $isArray = false, $throw = true)
    {
        //$models = User::findManyFromCache($ids);
        $resource = is_null($resource) ? $this->resource->getResourceCode(get_called_class()) : $resource;
        $model = $this->getModelObj($resource);
        $total = $model->count();
        if ($total > 5000) {
            if ($throw) {
                return $this->throwException('数据太多');
            }
            return false;
        }
        return $this->_cacheDatas($this->config->get('app_code'), $resource, $type, $simple, $isArray);
    }

    protected function _cacheDatas($app, $resource, $type, $simple, $isArray)
    {
        $model = $this->getModelObj($resource);
        $keyField = $model->getKeyName();
        $infos = $model->all();
    
        return $this->formatResultInfos($infos, $keyField, $type, $simple, $isArray);
    }*/

    public function getPointCaches($resource, $type = 'common', $app = null, $returnArray = true, $redis = null)
    {
        $app = is_null($app) ? $this->getAppcode() : $app;
        $key = $this->formatKey($resource, $app, $type);
        $values = $this->getCacheValues($key, $redis, $returnArray);
        return $values;
    }

    protected function getCacheValues($key, $redis = null, $returnArray = true)
    {
        $service = $this->getServiceObj('redis');
        if (!empty($redis)) {
            $service->setRedis($redis);
        }
        return $service->get($key, $returnArray);
    }

    protected function formatKey($resource, $app, $type)
    {
        $key = "{$app}:{$resource}:{$type}";
        return $key;
    }

    public function setPointCaches($resource = null, $infos = null, $type = 'common', $redis = null)
    {
        $resource = is_null($resource) ? $this->resource->getResourceCode(get_called_class(), false) : $resource;
        $app = $this->getAppcode();
        $datas = $this->formatCacheDatas($resource, $type, $infos);
        $key = $this->formatKey($resource, $app, $type);
        $service = $this->getServiceObj('redis');
        if (!empty($redis)) {
            $service->setRedis($redis);
        }
        return $service->set($key, $datas);
    }


    public function formatCacheDatas($resource, $type, $infos)
    {
        if (!empty($infos)) {
            return $infos;
        }

        $model = $this->getModelObj($resource);
        $infos = $model->all();
        $infos = $infos->keyBy($model->getKeyName());
        return $infos;
    }
}
