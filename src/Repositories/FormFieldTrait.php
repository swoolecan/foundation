<?php

declare(strict_types = 1);

namespace Swoolecan\Foundation\Repositories;

trait FormFieldTrait
{
    public function getFormatFormFields($scene)
    {
        $fields = $this->getSceneFields($scene);
        $defaultFormFields = $this->getDefaultFormFields();
        $formFields = $this->getFormFields();
        $fieldNames = $this->getAttributeNames($scene);
        $datas = [];
        foreach ($fields as $field) {
            $defaultFormField = $defaultFormFields[$field] ?? [];
            $formField = $formFields[$field] ?? [];
            $data = array_merge($defaultFormField, $formField);
            $data = empty($data) ? ['type' => 'input'] : $data;
            if (in_array($data['type'], ['radio', 'select']) && !isset($data['infos'])) {
                $data['infos'] = (object) $this->getKeyValues($field);
            }
            if (in_array($data['type'], ['file']) && !isset($data['resource'])) {
                $data['resource'] = $this->resource->getResourceCode(get_called_class(), false);
            }
            if (in_array($data['type'], ['file']) && !isset($data['app'])) {
                $data['app'] = $this->getAppcode();//config('app_code');
            }
            if (in_array($data['type'], ['complexSelectSearch', 'selectSearch', 'selectSearchInput']) && !isset($data['searchApp'])) {
                $data['searchApp'] = $this->getAppcode();
            }
            $data['options'] = $fieldNames[$field] ?? ['name' => $field];
            $datas[$field] = $data;
        }

        return $datas;
    }

    public function getDefaultFormFields()
    {
        return [
            'nickname' => ['type' => 'input', 'require' => ['add']],
            'description' => ['type' => 'input', 'typeExt' => 'textarea', 'rows' => 2],
            'user_id' => ['type' => 'selectSearch', 'require' => ['add'], 'searchResource' => 'user', 'searchApp' => 'passport'],
            'status' => ['type' => 'radio'],
            'thumb' => ['type' => 'file', 'filetype' => 'image', 'minnum' => 1, 'maxnum' => 10],
            'photo' => ['type' => 'file', 'filetype' => 'image', 'minnum' => 1, 'maxnum' => 10],
            'cover' => ['type' => 'file', 'filetype' => 'image', 'minnum' => 1, 'maxnum' => 10],
            'logo' => ['type' => 'file', 'filetype' => 'image', 'minnum' => 1, 'maxnum' => 1],
            'picture' => ['type' => 'file', 'filetype' => 'image', 'minnum' => 1, 'maxnum' => 10],
            'area' => ['type' => 'cascader'],
            //'content' => ['type' => 'editor'],
            'content' => ['type' => 'markdown'],
        ];
    }

    public function getFormFields()
    {
        return [];
    }
}
