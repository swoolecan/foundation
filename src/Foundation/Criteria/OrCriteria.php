<?php 
declare(strict_types = 1);

namespace Swoolecan\Foundation\Criteria;

class OrCriteria extends Criteria
{
    public function _pointApply($query, $repository)
    {
        $field = $this->getField();
        if (empty($field)) {
            return $query;
        }
        $operator = $this->params['operator'];
        $value = $this->params['value'];
        $query->where($field, $operator, $value);

        return $query;
    }
}
