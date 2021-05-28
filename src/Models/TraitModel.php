<?php

declare(strict_types = 1);

namespace Swoolecan\Foundation\Models;

trait TraitModel
{

    public function getColumnElems($type = 'keyValue')
    {
        $results = $this->getConnection()->getSchemaBuilder()->getColumnTypeListing($this->getTable());
        $datas = [];
        if ($type == 'keyValue') {
            $datas = [];
            foreach ($results as $result) {
                $datas[$result['column_name']] = empty($result['column_comment']) ? $result['column_name'] : $result['column_comment'];
            }
            return $datas;
        }
        return $results;
    }

    /**
     * Get the table associated with the model.
     *
     * @return string
     */
    public function getTable()
    {
        return $this->table ?? Str::snake(class_basename($this));
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
}
