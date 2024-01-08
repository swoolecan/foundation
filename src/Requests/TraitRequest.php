<?php

declare(strict_types = 1);

namespace Swoolecan\Foundation\Requests;

trait TraitRequest
{
    protected $_scene;
    protected $_repository;
    protected $_info;
    public $allowEmpty = false;

    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $scene = $this->getScene();
        $method = "_{$scene}Rule";
        if (!method_exists($this, $method)) {
            return [];
        }

        $data = [];
        $fields = $this->getRepository()->getSceneFields($scene);
        foreach ($fields as $field) {
            $data[$field] = [];
        }
        return array_merge($data, $this->$method());
    }

    public function routeParam(string $key, $default)
    {
        $route = $this->getCurrentRoute();
        if (is_null($route)) {
            return $default;
        }
        return array_key_exists($key, $route->params) ? $route->params[$key] : $default;
    }

    public function setScene($scene)
    {
        $this->_scene = $scene;
    }

    public function getScene()
    {
        return $this->_scene;
    }

    public function setRepository($repository)
    {
        $this->_repository = $repository;
    }

    public function getRepository()
    {
        return $this->_repository;
    }

    public function setInfo($info)
    {
        $this->_info = $info;
    }

    public function getInfo()
    {
        return $this->_info;
    }

    public function getInputDatas($type)
    {
        $method = "_{$type}Rule";
        $inputs = $this->all();
        /*if (!method_exists($this, $method)) {
            return $inputs;
        }*/

        $fields = $this->getRepository()->getSceneFields($type);
        $data = [];
        $check = true;

        foreach ($fields as $field) {
            if (isset($inputs[$field])) {
                $data[$field] = $inputs[$field];
            }
        }
        //$this->getRepository()->fillable(array_keys($data));
        $r = $this->getRepository()->unguard(true);
        return $data;
    }

    public function filterDirtyData($data)
    {
        return $data;
    }

    public function getCurrentRoute()
    {
        return null;
    }

    protected function _getKeyValues($field)
    {
        return [];
    }

    public function messages(): array
    {
        return array_merge($this->_messages(), [
            'name.required' => '请填写用户名',
            'password_old.required' => '请填写正确的原密码',
            'name.unique' => '用户名已存在',
            'mobile.required' => '请填写手机号',
            'mobile' => '手机号格式有误',
            'mobile.mobile' => '手机号格式有误',
            'mobile.unique' => '手机号已存在',
            'name.min' => '用户名最少3个字符',
            'name.max' => '用户名最多60个字符',
            'password.min' => '密码最少6个字符',
            'password.max' => '密码最多20个字符',
            'realname.required' => '姓名必填',
            'password.confirmed' => '两次输入的密码不一致',
            'nickname.required' => '真实姓名必填',
            'nickname.between' => '真实姓名长度为2-60个字符',
        ]);
    }

    protected function _messages()
    {
        return [];
    }

    public function checkInfo($info, $data)
    {
        return true;
    }
}
