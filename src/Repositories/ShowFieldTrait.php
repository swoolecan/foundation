<?php

declare(strict_types = 1);

namespace Swoolecan\Foundation\Repositories;

trait ShowFieldTrait
{
    public function getFormatShowFields($scene, $model, $simple = false)
    {
        $fields = $this->getSceneFields($scene);
        $defaultShowFields = $this->getDefaultShowFields();
        $showFields = $this->getShowFields();
        $datas = [];
        foreach ($fields as $field) {
            if ($field == 'point_operation') {
                $datas[$field] = $this->getPointOperation($model, $field);
                continue;
            }
            $value = $model->$field;
            $defaultShowField = $defaultShowFields[$field] ?? [];
            $showField = $showFields[$field] ?? [];
            $data = array_merge($defaultShowField, $showField);
            if (empty($data)) {
                $datas[$field] = $simple ? $value : ['showType' => 'common', 'value' => $value, 'valueSource' => $value];
                continue ;
            }

            $data['valueSource'] = $value;
            $data['showType'] = $data['showType'] ?? 'common';
            $valueType = $data['valueType'] ?? 'self';

            if ($valueType == 'key') {
                $value = $this->getKeyValues($field, $model->$field);
            } elseif ($valueType == 'select') {
                $value = $this->getKeyValues($field);
            } elseif ($valueType == 'link') {
                $value = $model->$field;
                if (!empty($value)) {
                    $showName = $data['showName'] ?? $value;
                    $value = "<a href='{$value}' target='_blank'>{$showName}</a>";
                }
            } elseif ($valueType == 'point') {
                $relate = $data['relate'];
                $relate = $relate ? $model->$relate : false;
                $relateField = $data['relateField'] ?? 'name';
                $value = $relate ? $relate->$relateField : $value;
            } elseif ($valueType == 'cache') {
                $relate = $data['relate'];
                $relate = $relate ? $this->get($relate) : false;
                $relateField = $data['relateField'] ?? 'name';
                $value = $relate ? $relate[$relateField] : $value;
            } elseif ($valueType == 'rpc') {
                $value = $this->getRpcData($data['app'], $data['relate'], $value, $data['keyField']);
            } elseif ($valueType == 'callback') {
                $method = $data['method'];
                $value = $this->$method($model, $field);
            } elseif ($valueType == 'datetime') {
                $value = empty($model->$field) ? '' : $model->$field->toDateTimeString();
                $data['valueSource'] = $value;
            } elseif ($valueType == 'region') {
                $value = $this->getRegionData($model->$field, true);
                $data['valueSource'] = $value;
            } elseif ($valueType == 'extinfo') {
                $method = ucfirst($data['extType']);
                $method = "get{$method}";
                $extInfo = $model->$method($field);
                $value = $extInfo['show'];
                $data['valueSource'] = $extInfo['source'];
            } elseif ($valueType == 'file') {
                $value = $model->getAttachmentInfos(['info_field' =>$field]);
                $data['valueSource'] = [];
                if (!empty($value)) {
                    foreach ($value as $fileDetail) {
                        $data['valueSource'][] = $fileDetail['id'];
                    }
                }
            } elseif ($valueType == 'popover') {
                $strLen = $data['strLen'] ?? 20;
                $suffix = $strLen < $this->resource->strOperation($value, 'length') ? '...' : '';
                $value = $this->resource->strOperation($value, 'substr', ['start' => 0, 'length' => $strLen]) . $suffix; 
            }
            $data['value'] = $value;
            $datas[$field] = $simple ? $value : $data;
        }

        return $datas;
    }

    public function getDefaultShowFields()
    {
        return [
            'description' => ['showType' => 'popover', 'valueType' => 'popover'],
            //'description' => ['showType' => 'edit'],
            'publish_at' => ['showType' => 'edit'],
            'status' => ['valueType' => 'key'],
            //'orderlist' => ['showType' => 'edit'],
            'url' => ['valueType' => 'link', 'showName' => 'URL'],
            'baidu_url' => ['valueType' => 'link', 'showName' => '百度百科'],
            'wiki_url' => ['valueType' => 'link', 'showName' => '维基百科'],
            //'baidu_url' => ['showType' => 'edit'],
            //'baidu_url_show' => ['valueType' => 'link', 'showName' => '百度百科'],
            'logo' => ['showType' => 'file', 'valueType' => 'file'],
            'province_code' => ['valueType' => 'region'],
            'city_code' => ['valueType' => 'region'],
            'county_code' => ['valueType' => 'region'],
            'cover' => ['showType' => 'file', 'valueType' => 'file'],
            'thumb' => ['showType' => 'file', 'valueType' => 'file'],
            'photo' => ['showType' => 'file', 'valueType' => 'file'],
            'picture' => ['showType' => 'file', 'valueType' => 'file'],
            'created_at' => ['valueType' => 'datetime'],
            'updated_at' => ['valueType' => 'datetime'],
            'user_id' => ['valueType' => 'point', 'relate' => 'user'],
            'region_code' => ['valueType' => 'rpc', 'relate' => 'region', 'app' => 'passport', 'keyField' => 'code'],
        ];
    }

    public function getShowFields()
    {
        return [];
    }

    public function getPointOperation($model, $field)
    {
        return [
            'showType' => 'operation',
            'operations' => $this->_pointOperations($model, $field),
        ];
    }

    protected function _pointOperations($model, $field)
    {
        return [];
    }

    public function getHaveSelection($scene)
    {
        return false;
    }

    public function getSelectionOperations($scene)
    {
        return [];
    }
}
