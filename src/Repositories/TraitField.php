<?php

declare(strict_types = 1);

namespace Wangcan\Foundation\Repositories;

trait TraitField
{
    use TraitFormField;
    use TraitShowField;
    use TraitSearchField;

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
            'orderlist' => ['width' => '80'],
            'logo' =>['width' => '150'],
            'picture' =>['width' => '150'],
            'thumb' =>['width' => '150'],
            'title' => ['width' => '200', 'rowNum' => 1, 'withPop' => 1],
            'created_at' => ['width' => '160'],
            'point_operation' => ['width' => '160', 'name' => '特定操作'],
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
        $method = "_{$elem}KeyDatas";
        $datas = $this->$method();
        if (is_null($value)) {
            return $datas;
        }
        if (isset($datas[$value])) {
            return $datas[$value];
        }
        return $value;
    }
}
