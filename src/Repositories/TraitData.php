<?php

declare(strict_types = 1);

namespace Wangcan\Foundation\Repositories;
use Hyperf\Cache\Annotation\CachePut;
use Hyperf\Cache\Annotation\Cacheable;

trait TraitData
{
    /*public function getPointValues($resourceCode)
    {
        return array_merge($this->model->getColumnElems(), $this->extAttributeNames());
    }*/

    public function getCacheOutData($app, $resource, $key, $keyField = 'id')
    {
        $app = ucfirst($app);
        $class = "\Wangcan\Foundation\RpcClient\\{$app}RpcClient";
        $client = make($class);
        return $client->getCacheData($app, $resource, $key, $keyField);
    }

    /*public function getSingleAttachmentData($app, $params)
    {
        $currentAppCode = config('app_code');
        if ($currentAppCode == 'passport') {
            return $this->getCacheData('attachmentInfo', $params);
        }

        return $this->getCacheOutData('passport', 'attachmentInfo', $params);
    }*/

    public function getAttachmentInfos($params)
    {
        $currentAppCode = config('app_code');
        if ($currentAppCode == 'passport') {
            return $this->resource->getObject('repository', 'attachmentInfo')->getDatas($params);
        }
        $class = "\Wangcan\Foundation\RpcClient\PassportRpcClient";
        $client = make($class);
        return $client->getAttachmentInfos($params);
    }

    public function getCacheData($resource, $key)
    {
        return $this->getModelObj($resource)->findCacheData($key);
    }

    public function getCacheDatas($resource = null, $type = 'list', $simple = true, $isArray = false, $throw = true)
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

    /**
     * @Cacheable(prefix="fulltable-cache")
     */
    protected function _cacheDatas($app, $resource, $type, $simple, $isArray)
    {
        $model = $this->getModelObj($resource);
        $keyField = $model->getKeyName();
        $infos = $model->all();
    
        return $this->formatResultInfos($infos, $keyField, $type, $simple, $isArray);
    }

    protected function formatResultInfos($infos, $keyField, $type, $simple, $isArray)
    {
        $datas = [];
        foreach ($infos as $info) {
            $formatInfo = $this->getFormatShowFields($type, $info, $simple);
            $datas[$info[$keyField]] = $formatInfo;
        }
        if ($isArray) {
            return array_values($datas);
        }
        return $datas;
    }

    public function getPointKeyValues($resource = null, $where = [], $scene = 'keyvalue')
    {
        $repository = is_null($resource) ? $this : $this->resource->getObject('repository', $resource);
        $datas = $repository->findWhere($where);
        $collectionClass = $repository->getCollectionClass();
        $collection = new $collectionClass($datas, $scene, $repository);
        return $collection->toArray();
    }
}
