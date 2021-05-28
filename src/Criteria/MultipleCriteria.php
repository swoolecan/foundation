<?php 
declare(strict_types = 1);

namespace Swoolecan\Foundation\Criteria;

class MultipleCriteria extends Criteria
{
    public function _pointApply($query, $repository)
    {
        $field = $this->getField();
        if (empty($field)) {
            return $query;
        }
        $operator = $this->params['operator'];
        $mark = $this->params['mark'] ?? ',';
        $values = array_filter(explode($mark, $this->params['value']));
        foreach ($values as $value) {
            $query->orWhere($field, $operator, $value);
        }

        return $query;
    }
}
