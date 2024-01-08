<?php

declare(strict_types = 1);

namespace Swoolecan\Foundation\Repositories;

trait DataTrait
{
    use CacheRpcTrait;

    public function getRegionData($code, $returnName = false)
    {
        $regions = $this->getPointCaches('region', 'common', 'passport');
        $region = $regions[$code] ?? [];
        return $returnName ? ($region['name'] ?? '') : $region;
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
        $collection = $this->getCollectionObj($datas, $scene, $resource);
        return $collection->toArray();
    }
}
