<?php 
declare(strict_types = 1);

namespace Wangcan\Foundation\Criteria;

class BetweenCriteria extends Criteria
{
    public function _pointApply($query, $repository)
    {
        $field = $this->getField();
        if (empty($field)) {
            return $query;
        }
        $value = $this->params['value'];
        $value = explode('|', $value);

        $query->whereBetween($field, $value);

        return $query;
    }
}
