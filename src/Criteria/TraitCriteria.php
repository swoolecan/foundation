<?php
declare(strict_types = 1);

namespace Swoolecan\Foundation\Criteria;

trait TraitCriteria
{
    protected $field;
    protected $value;

    public function _pointApply($query, $repository)
    {
        return $query;
    }

    public function getField()
    {
        return isset($this->params['field']) ? $this->params['field'] : false;
    }

    /**
     * @param $model
     * @param RepositoryInterface $repository
     * @return mixed
     */
    public function _applyBase($query, $repository)
    {
        $field = $this->getField();
        if (empty($field)) {
            return $query;
        }
        $value = $this->params['value'];
        $operator = $this->params['operator'];
        $query->where($field, $operator, $value);

        return $query;
    }
}
