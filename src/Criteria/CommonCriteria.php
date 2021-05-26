<?php 
declare(strict_types = 1);

namespace Wangcan\Foundation\Criteria;

class CommonCriteria extends Criteria
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
        
        $query->where($field, $operator, $value);
        //echo $query->toSql() . '=======';

        return $query;
    }
}
