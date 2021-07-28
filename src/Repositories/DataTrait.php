<?php

declare(strict_types = 1);

namespace Swoolecan\Foundation\Repositories;

trait DataTrait
{
    use CacheRpcTrait;

    public function getAttachmentInfo($params)
    {
        return $this->getAttachmentInfos($params, true);
    }

    public function getAttachmentUrl($params)
    {
        return $this->getAttachmentInfos($params, true, true);
    }

    public function getRegionData($code, $returnName = false)
    {
        $regions = $this->getPointCaches('region', 'common', 'passport');
        $region = $regions[$code] ?? [];
        return $returnName ? ($region['name'] ?? '') : $region;
    }

    public function getAttachmentInfos($params, $isSingle = false, $onlyUrl = false)
    {
        $currentAppcode = $this->getAppcode();//config('app_code');
        $params['app'] = $params['app'] ?? $currentAppcode;
        if ($currentAppcode == 'passport') {
            $attachmentInfo = $this->resource->getObject('repository', 'attachmentInfo');
            return $isSingle ? $attachmentInfo->getData($params, $onlyUrl) : $attachmentInfo->getDatas($params);
        }
        $class = "\Framework\Baseapp\RpcClient\PassportRpcClient";
        $client = $this->resource->getObjectByClass($class);
        return $isSingle ? $client->getAttachmentInfo($params, $onlyUrl) : $client->getAttachmentInfos($params);
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
        $repository = is_null($resource) ? $this : $this->getRepositoryObj($resource);
        $datas = $repository->findWhere($where);
        $collection = $this->getCollectionObj($resource, ['resource' => $datas, 'scene' => $scene, 'repository' => $repository]);
        return $collection->toArray();
    }
}
