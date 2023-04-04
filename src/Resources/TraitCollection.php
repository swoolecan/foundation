<?php

declare(strict_types = 1);

namespace Swoolecan\Foundation\Resources;

use Swoolecan\Foundation\Helpers\TraitResourceManager;

trait TraitCollection
{
    use TraitResourceManager;
    protected $_scene = 'list';
    protected $_model;
    protected $repository;
    protected $simpleResult;
    protected $params;

    /**
     * Create a new resource instance.
     *
     * @param mixed $resource
     */
    public function __construct($params = [])
    {
        $this->setScene($params['scene']);
        //$this->repository = $params['repository'];
        $this->repository = $this->getRepositoryObj();
        $this->simpleResult = $params['simpleResult'] ?? false;
        parent::__construct($params['resource']);
    }

    /**
     * Transform the resource collection into an array.
     *
     * @return array
     */
    public function toArray($request = null) :array
    {
        $scene = $this->getScene();
        $method = "_{$scene}Array";
        if (method_exists($this, $method)) {
            return $this->$method();
        }
        return [];
    }

    protected function _keyvalueArray()
    {
        $datas = $this->collection->toArray();
        $result = [];
        $key = $this->getModel()->getKeyField();
        $name = $this->getModel()->getNameField();
        foreach ($datas as $data) {
            $result[$data[$key]] = $data[$name];
        }
        return $result;
    }

    protected function _keyvalueExtArray()
    {
        return [
            'key' => $this->getModel()->getKeyField(),
            'name' => $this->getModel()->getNameField(),
            'extField' => 'extField',
            'extField2' => 'extField2',
            'data' => $this->collection->toArray(),
        ];
        $result = [];
        foreach ($datas as $data) {
            $tmp = array_values($data);
            $result[$tmp[0]] = $tmp[1];
        }
        return ['data' => $result];
    }

    protected function _treeArray()
    {
        $addFormFields = $this->repository->getFormatFormFields('add');
        $updateFormFields = $this->repository->getFormatFormFields('update');
        return [
            'data' => $this->collection,
            'links' => [
                'self' => 'link-value',
            ],
            'fieldNames' => $this->repository->getAttributeNames('list'),
            'addFormFields' => $addFormFields ? $addFormFields : (object)[],
            'updateFormFields' => $updateFormFields ? $updateFormFields : (object)[],
        ];
    }

    protected function _popArray()
    {
        $searchFields = $this->repository->getFormatSearchFields($this->getScene() . 'Search');
        return [
            'data' => $this->collection,
            'links' => [
                'self' => 'link-value',
            ],
            'fieldNames' => $this->repository->getAttributeNames($this->getScene()),
            'searchFields' => $searchFields ? $searchFields : (object)[],
            'haveSelection' => $this->repository->getHaveSelection($this->getScene()),
            'selectionOperations' => $this->repository->getSelectionOperations($this->getScene()),
        ];
    }

    protected function _listArray()
    {
        $addFormFields = $this->repository->getFormatFormFields('add');
        $updateFormFields = $this->repository->getFormatFormFields('update');
        $searchFields = $this->repository->getFormatSearchFields($this->getScene() . 'Search');
        return [
            'data' => $this->collection,
            'links' => [
                'self' => 'link-value',
            ],
            'fieldNames' => $this->repository->getAttributeNames($this->getScene()),
            'addFormFields' => $addFormFields ? $addFormFields : (object)[],
            'updateFormFields' => $updateFormFields ? $updateFormFields : (object)[],
            'searchFields' => $searchFields ? $searchFields : (object)[],
            'haveSelection' => $this->repository->getHaveSelection($this->getScene()),
            'selectionOperations' => $this->repository->getSelectionOperations($this->getScene()),
        ];
    }

    public function getModel()
    {
        if (empty($this->_model)) {
            $this->_model = $this->getModelObj();
        }
        return $this->_model;
        //return $this->_model ?? $this->collection->first();
    }

    public function setModel($model)
    {
        $this->_model = $model;
    }

    public function setScene($scene = 'list')
    {
        $this->_scene = $scene;
    }

    public function getScene()
    {
        return $this->_scene;
    }
}
