<?php

declare(strict_types = 1);

namespace Swoolecan\Foundation\Repositories;

trait TreeTrait
{
    public function getPointTreeDatas($resource = null, $level = 2, $type = 'keyvalue', $isArray = true, $simple = true)
    {
        $resource = is_null($resource) ? $this->resource->getResourceCode(get_called_class()) : $resource;
        //$app = $this->config->get('app_code');
        $app = $this->getAppcode();
        return $this->_pointTreeDatas($app, $resource, $level, $type, $isArray, $simple);
    }

    public function _pointTreeDatas($app, $resource, $level, $type, $isArray, $simple)
    {
        $model = $this->getModelObj($resource);
        $keyField = $model->getKeyName();
        $parentField = $model->getParentField($keyField);
        $parentFirstValue = $model->getParentFirstValue($keyField);
        $infos = $model->where($parentField, $parentFirstValue)->get();
        $datas = $this->formatResultInfos($infos, $keyField, $type, $simple, $isArray);
        if ($level == 1) {
            return $datas;
        }

        foreach ($datas as & $data) {
            $subInfos = $model->where($parentField, $data[$keyField])->get();
            $subDatas = $this->formatResultInfos($subInfos, $keyField, $type, $simple, $isArray); 
            if ($level > 2) {
                foreach ($subDatas as & $subData) {
                    $thirdInfos = $model->where($parentField, $subData[$keyField])->get();
                    $thirdDatas = $this->formatResultInfos($thirdInfos, $keyField, $type, $simple, $isArray); 
                    $subData['subInfos'] = $thirdDatas;
                }
            }
            $data['subInfos'] = $subDatas;
        }
        return $datas;
    }

    public function getTreeInfos($infos = null, $type = 'list', $forceArray = true, $simple = true)
    {
        $model = $this->model;
        if (is_null($infos)) {
            $total = $model->count();
            if ($total > 5000) {
                return $this->throwException('数据太多');
            }
            $infos = $this->getInfosForTree();
        }
        $keyField = $model->getKeyName();
        $parentField = $model->getParentField($keyField);
        $parentFirstValue = $model->getParentFirstValue($keyField);
        $infos = $this->_formatTreeDatas($infos, $keyField, $parentField, $parentFirstValue, $type, $forceArray, $simple);
        return $infos;
    }

    public function getTreeLists($infos = null, $type = 'list', $forceArray = true, $simple = false)
    {
        $infos = $this->getTreeInfos($infos, $type, $forceArray, $simple);
        $addFormFields = $this->getFormatFormFields('add');
        $updateFormFields = $this->getFormatFormFields('update');
        return [
            'data' => $infos,
            'fieldNames' => $this->getAttributeNames($type),
            'addFormFields' => $addFormFields ? $addFormFields : (object)[],
            'updateFormFields' => $updateFormFields ? $updateFormFields : (object)[],
        ];
    }

    public function getParentChains($info)
    {
        $model = $this->getModelObj();
        $keyField = $model->getKeyName();
        $parentField = $model->getParentField($keyField);
        $parentFirstValue = $model->getParentFirstValue($keyField);

        $parents = [];
        $currentInfo = $info;
        $parentValue = $info[$parentField];
        while ($parentValue != $parentFirstValue) {
            $parent = $model->find($parentValue);
            $parentValue = $parentFirstValue;
            if (!empty($parent)) {
                $parents[] = $parent;
                $parentValue = $parent[$parentField];
            }
        }
        $parents = array_reverse($parents);
        return $parents;
    }

    public function _formatTreeDatas($infos, $key, $parentKey, $parent, $type = 'list', $forceArray = true, $simple = false)
    { 
        $datas = [];
        foreach ($infos as $iKey => $info) { 
            //$info = $iValue->toArray();    
            if ($info[$parentKey] == $parent) {
                unset($infos[$iKey]);
                $formatInfo = $this->getFormatShowFields($type, $info, $simple);
                //$formatInfo = $info->toArray();
                $formatInfo['subInfos'] = $this->_formatTreeDatas($infos, $key, $parentKey, $info[$key], $type, $forceArray, $simple);
                //$formatInfo['hasChildren'] = count($formatInfo['subInfos']) > 0 ? true : false;
                $keyField = $info->getKeyName();
                $formatInfo['keyField'] = $info->$keyField;
                if ($forceArray) {
                    $datas[] = $formatInfo;
                } else {
                    $datas[$info[$key]] = $formatInfo;   
                }
            }
        }
        return $datas;        
    }

    public function getInfosForTree($orderField = 'orderlist')
    {
        return $this->model->orderBy($orderField, 'desc')->get();
    }
}
