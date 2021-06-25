<?php
declare(strict_types = 1);

namespace Swoolecan\Foundation\Resources;

trait TraitResource
{
    protected $_scene;
    protected $_repository;
    protected $_simpleResult;
    public $preserveKeys = true;

    public function __construct($params = [])//$resource, $scene, $repository, $simpleResult = false)
    {
        $this->setScene($params['scene']);
        $this->_repository = $params['repository'] ?? null;
        $this->_simpleResult = $params['simpleResult'] ?? false;
        parent::__construct($params['resource']);
    }

    /**
     * Transform the resource into an array.
     *
     * @return array
     */
    public function toArray($request = null): array
    {
        $scene = $this->getScene();
        $method = "_{$scene}Array";
        if (method_exists($this, $method)) {
            return $this->$method();
        }
        return $this->_keyvalueArray();
    }

    public function setRepository($repository)
    {
        $this->_repository = $repository;
    }

    public function getRepository()
    {
        return $this->_repository;
    }

    public function setScene($scene)
    {
        $this->_scene = $scene;
    }

    public function setSimpleResult($simpleResult)
    {
        $this->_simpleResult = $simpleResult;
    }

    public function getSimpleResult()
    {
        return $this->_simpleResult;
    }

    public function getScene()
    {
        return $this->_scene;
    }

    protected function _keyvalueArray()
    {
        $keyField = $this->resource->getKeyName();
        return [
            $keyField => $this->$keyField,
            'name' => $this->name,
        ];
    }

    protected function _viewArray()
    {
        return $this->getRepository()->getFormatShowFields('view', $this->resource, $this->getSimpleResult());
    }

    protected function _popArray()
    {
        return $this->getRepository()->getFormatShowFields('pop', $this->resource, $this->getSimpleResult());
    }

    protected function _listArray()
    {
        return $this->getRepository()->getFormatShowFields('list', $this->resource, $this->getSimpleResult());
    }
}
