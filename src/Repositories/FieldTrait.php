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
            'point_operation' => ['width' => '160', 'name' => '特定操作', 'nosort' => 1],
            'ftitle' =>['name' => '头衔', 'width' => '100'],
            'birthday' =>['name' => '出生日期', 'width' => '100'],
            'deathday' =>['name' => '去世日期', 'width' => '100'],
            'status' => ['name' => '状态'],
            'updated_at' => ['name' => '更新时间'],
            'created_at' => ['name' => '创建时间'],
            'password' => ['name' => '密码', 'hidden' => 1],
            'password_confirmation' => ['name' => '确认密码', 'hidden' => 1],
            'note' => ['name' => '备注'],
            'import_file' => ['name' => '导入文件'],

            'total_number' => ['name' => '库存总数量'],
            'reserved_number' => ['name' => '当前库存占用数量'],
            'out_number' => ['name' => '本次取货数量'],
            'material_number' => ['name' => '货品数量', 'width' => '60'],
            'locker_code' => ['name' => '料箱代码'],
            'material_name' => ['name' => '货品名称', 'nosort' => 1],
            'material_barcode' => ['name' => '货品条码', 'nosort' => 1],
            'add_number' => ['name' => '新增数量'],
            'new_number' => ['name' => '调整后数量'],
            'receipt_type' => ['name' => '入库类型'],
            'agv_number' => ['name' => 'AGV数量'],
            'locker_number' => ['name' => '料箱数量'],
            'workstation_code_target' => ['name' => '前往工作站'],
            'workstation_code_current' => ['name' => '取货工作站'],
            'workstation_code' => ['name' => '工作站代码'],
            'material_picture' => ['name' => '图示', 'nosort' => 1],
            'material_code_format' => ['name' => '货品编号', 'nosort' => 1],
            'occupy_number' => ['name' => '占用数'],
            'fetch_zone' => ['name' => '取货货位'],
            'workstation_number' => ['name' => '工作站编号', 'width' => '120'],
            'inventory_yesterday' => ['name' => '昨日库存'],
            'other_area' => ['name' => '取货库位'],
            //'' => ['name' => ''],
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

    public function getIgnoreOperations($scene)
    {
        return $this->getSceneFields($scene . 'IgnoreOperation');
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
