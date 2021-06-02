<?php

declare(strict_types = 1);

namespace Swoolecan\Foundation\Models;

trait TraitModel
{
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
