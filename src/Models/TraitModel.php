<?php

declare(strict_types = 1);

namespace Swoolecan\Foundation\Models;

use Swoolecan\Foundation\Helpers\TraitResourceManager;

trait TraitModel
{
    use TraitResourceManager;
    use CommonDataTrait;
    public $withOperator = false;

    public static $status = [
        0 => '禁用',
        1 => '正常'
    ];
    
    public function getFormatState($key = 0, $enum = array(), $default = '')
    {
        return array_key_exists($key, $enum) ? $enum[$key] : $default;
    }

    public function getParentField($keyField = 'id')
    {
        return "parent_{$keyField}";
    }

    public function getParentFirstValue($keyField = 'id')
    {
        return $keyField == 'id' ? 0 : '';
    }

    public function getKeyField()
    {
        return $this->getKeyName();
    }

    public function getNameField()
    {
        return 'name';
    }

    public function canDelete()
    {
        return true;
    }

    /*protected $attributes = [
        'status' => 1,
    ];

    public function getStatusTextAttribute()
    {
        return $this->attributes['status_text'] = $this->getFormatState($this->attributes['status'], self::$status);
    }*/

    /*public function getList(array $params, int $pageSize)
    {
        $params = [
            'sort_name' => 'id',
            'sort_value' => 'desc',
        ];
        $list = $this->query()->orderBy($params['sort_name'], $params['sort_value'])->paginate($pageSize);
        foreach ($list as &$value) {
            $value->sex_text;
            $value->status_text;
        }
        return $list;
    }*/

    public function fieldTypes()
    {
        return [
            'created_at' => 'timestamp',
            'updated_at' => 'timestamp',
            'last_at' => 'timestamp',
            'status' => 'checkbox',
            'type' => 'dropdown',
        ];
    }

    public function getLevelInfos($level, $parentValue = null, $datas = [])
    {
        if (empty($level)) {
            return $datas;
        }

        $keyField = $this->getKeyName();
        $parentField = $this->getParentField($keyField);
        $parentValue = is_null($parentValue) ? $this->getParentFirstValue($keyField) : $parentValue;
        $infos = $this->where($parentField, $parentValue)->get();
        $datas = array_merge($datas, (array) $infos);
        if ($level) {
            foreach ($infos as $info) {
                $datas = $this->getLevelInfos($level - 1, $info[$parentField], $datas);
            }

        }
        return $datas;
    }

    public function addNotExist($data, $fields)
    {
        $where = [];
        foreach ($fields as $field) {
            $where[$field] = $data[$field];
        }
        $exist = $this->where($where)->first();
        if ($exist) {
            return $exist;
        }
        return $this->create($data);
    }

    public function createWithOperator($return = 'object')
    {
        if ($this->withOperator) {
            $info = request()->get('current_user');
            if (empty($info)) {
                return false;
            }
            $this->operator_uid = $info['id'] ?? 0;
            $manager = $this->getModelObj('passport-manager')->where('user_id', $this->operator_uid)->first();
            $operatorName = $manager && $manager->nickname ? $manager->nickname : (isset($info['nickname']) && !empty($info['nickname']) ? $info['nickname'] : ($info['name'] ?? ''));
            $this->operator_name = $operatorName;
            if ($return == 'array') {
                return [
                    'operator_uid' => $this->operator_uid,
                    'operator_name' => $this->operator_name,
                ];
            }
        }
        return true;
    }
}
