<?php

declare(strict_types = 1);

namespace Swoolecan\Foundation\Repositories;
use Swoolecan\Foundation\Helpers\TraitResourceManager;

trait TraitRepository
{
    use TraitResourceManager;
    use DataTrait;
    use FieldTrait;
    use FormFieldTrait;
    use SearchFieldTrait;
    use ShowFieldTrait;
    use TreeTrait;

    public $currentScene;

    /**
     * @var query
     */
    protected $query;

    /**
     * @var bool
     */
    protected $skipCriteria = false;

    /**
     * Prevents from overwriting same criteria in chain usage
     * @var bool
     */
    protected $preventCriteriaOverwriting = false;

    public function __call($name, $arguments)
    {   
        return $this->model->{$name}(...$arguments);
    }

    /**
     * @param array $data
     * @return mixed
     */
    public function create(array $data)
    {
        $result = $this->model->create($data);
        return $result;
    }

    /**
     * save a model without massive assignment
     *
     * @param array $data
     * @return bool
     */
    public function saveModel(array $data)
    {
        foreach ($data as $k => $v) {
            $this->model->$k = $v;
        }
        return $this->model->save();
    }

    /**
     * @param $info
     * @param array $data
     * @return mixed
     */
    public function updateInfo($info, array $data)
    {
        $result = $info->update($data);
        return $info;
    }

    /**
     * @param $id
     * @return mixed
     */
    public function deleteInfo($info, $number)
    {
        /*$canDelete = $info->canDelete();
        if (empty($canDelete)) {
            $message = $number ? "已删除 {$number} 条信息" : '';
            $message .= $info[$info->getNameField()] . '信息无法被删除';
            return $this->resource->throwException(403, $message);
        }*/
        $result = $info->delete();
        return $result;
    }

    /**
     * Find data by id
     *
     * @param       $id
     * @param array $columns
     *
     * @return mixed
     */
    public function find($id, $columns = ['*'])
    {
        $this->applyCriteria();
        $this->applyScope();
        $model = $this->model->find($id, $columns);
        $this->resetModel();

        return $this->parserResult($model);
    }

    /**
     * Find data by multiple fields
     *
     * @param array $where
     * @param array $columns
     *
     * @return mixed
     */
    public function findWhereOne(array $where, $columns = ['*'])
    {
        $this->applyCriteria();
        $this->applyScope();

        $this->applyConditions($where);

        $model = $this->model->first($columns);
        $this->resetModel();

        return $this->parserResult($model);
    }

    /**
     * Retrieve all data of repository
     *
     * @param array $columns
     *
     * @return mixed
     */
    public function all($columns = ['*'])
    {
        $this->applyCriteria();
        $this->applyScope();

        if ($this->model instanceof Builder) {
            $results = $this->model->limit(2000)->get($columns);
        } else {
            $results = $this->model->limit(2000)->get($columns);
        }

        $this->resetModel();
        $this->resetScope();

        return $this->parserResult($results);
    }

    protected function applyCriteria()
    {
        return parent::applyCriteria();
    }
}
