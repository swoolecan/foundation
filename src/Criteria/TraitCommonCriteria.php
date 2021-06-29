<?php 
declare(strict_types = 1);

namespace Swoolecan\Foundation\Criteria;

trait TraitCommonCriteria
{
    public function _pointApply($query, $repository)
    {
        $field = $this->getField();
        if (empty($field)) {
            return $query;
        }
        $operator = $this->params['operator'];
        $value = $this->params['value'];
        if ($operator == 'like-left') {
            $operator = 'like';
            $value = "%{$value}";
        } elseif ($operator == 'like-right') {
            $operator = 'like';
            $value = "{$value}%";
        } else if ($operator == 'like') {
            $value = "%{$value}%";
        }
        
        $query = $query->where($field, $operator, $value);
        //echo $query->toSql() . '=======';

        return $query;
    }
}
