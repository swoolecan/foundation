<?php

declare(strict_types = 1);

namespace Swoolecan\Foundation\Repositories;

trait FieldTrait
{
    public function getAttributeNames($scene = null)
    {
        $datas = array_merge($this->model->getColumnElems(), $this->extAttributeNames());
        if (is_null($scene)) {
            return $datas;
        }
        $fields = $this->getSceneFields($scene);
        if (empty($fields)) {
            return $datas;
        }
        $result = [];
        $options  = $this->getFieldOptions();
        foreach ($fields as $field) {
            $default = [
                'name' => $datas[$field] ?? $field,
                'width' => 80,
                'align' => 'center',
            ];
            $result[$field] = isset($options[$field]) ? array_merge($default, $options[$field]) : $default;
        }
        return $result;
    }

    protected function getFieldOptions()
    {
        return array_merge([
            'id' => ['width' => '60'],
            'name' => ['width' => '80'],
            'description' => ['width' => '200'],
            'baidu_url' => ['width' => '80'],
            'orderlist' => ['width' => '80'],
            'logo' =>['width' => '150'],
            'picture' =>['width' => '150'],
            'thumb' =>['name' => '主图', 'width' => '150'],
            'photo' =>['name' => '主图', 'width' => '150'],
            'cover' =>['width' => '150'],
            'title' => ['width' => '200', 'rowNum' => 1, 'withPop' => 1],
            'created_at' => ['width' => '160'],
            'point_operation' => ['width' => '160', 'name' => '特定操作'],
            'ftitle' =>['name' => '头衔', 'width' => '100'],
            'birthday' =>['name' => '出生日期', 'width' => '100'],
            'deathday' =>['name' => '去世日期', 'width' => '100'],
            'status' => ['name' => '状态'],
            'updated_at' => ['name' => '更新时间'],
            'created_at' => ['name' => '创建时间'],
        ], $this->_getFieldOptions());
    }

    protected function _getFieldOptions()
    {
        return [];
    }

    protected function extAttributeNames()
    {
        return [];
    }

    public function getSceneFields($scene = null)
    {   
        $fields = $this->_sceneFields();  
        if (is_null($scene)) {
            return $fields;
        }

        if (isset($fields[$scene])) {
            return $fields[$scene];
        }
        return [];
    }

    protected function _sceneFields()
    {
        return [];
    }

    public function getKeyValues($elem, $value = null)
    {
        $method = $this->resource->strOperation($elem, 'camel');
        $method = "_{$method}KeyDatas";
        $datas = $this->$method();
        if (is_null($value)) {
            return $datas;
        }
        if (isset($datas[$value])) {
            return $datas[$value];
        }
        return $value;
    }

    public function getDefaultSort()
    {
        $model = $this->getModel();
        $sKey = $model->getKeyField();
        if ($sKey != 'id' && !$model->timestamps) {
            return [];
        }
        $sKey = $sKey != 'id' ? $model->getCreatedAtColumn() : $sKey;
        return [$sKey => 'desc'];
    }

    protected function _statusKeyDatas()
    {
        return [
            0 => '未激活',
            1 => '使用中',
            99 => '锁定',
        ];
    }
}
