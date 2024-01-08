<?php
declare(strict_types = 1);

namespace Swoolecan\Foundation\Resources;

use Swoolecan\Foundation\Helpers\TraitResourceManager;

trait TraitResource
{
    use TraitResourceManager;
    protected $_scene;
    protected $_repository;
    protected $_simpleResult;
    public $preserveKeys = true;

    public function __construct($resource = null, $params = [])//$scene, $repository, $simpleResult = false)
    {
        $this->setScene($params['scene'] ?? '');
        $this->_repository = $params['repository'] ?? null;
        $this->_simpleResult = $params['simpleResult'] ?? false;
        $resource = is_null($resource) ? $params['resource'] : $resource;
        parent::__construct($resource);
    }

    public function _toArray($request = null)
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
        if (empty($this->_repository)) {
            $this->_repository = $this->getRepositoryObj();
        }
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

    protected function _listArray($pointScene = 'list')
    {
        return $this->getRepository()->getFormatShowFields($pointScene, $this->resource, $this->getSimpleResult());
    }
}
