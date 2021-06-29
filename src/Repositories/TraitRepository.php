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
        $this->model->dealCreating($data);
        $result = $this->model->create($data);
        $result->dealCreated();
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
        $info->dealUpdating($data);
        $result = $info->update($data);
        $info->dealUpdated();
        return $result;
    }

    /**
     * @param $id
     * @return mixed
     */
    public function deleteInfo($info, $number)
    {
        $canDelete = $info->canDelete();
        if (empty($canDelete)) {
            $message = $number ? "已删除 {$number} 条信息" : '';
            $message .= $info[$info->getNameField()] . '信息无法被删除';
            return $this->resource->throwException(403, $message);
        }
        $info->dealDeleting();
        $result = $info->delete();
        $info->dealDeleted();
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
}
