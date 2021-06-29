<?php 
declare(strict_types = 1);

namespace Swoolecan\Foundation\Criteria;

trait TraitBetweenCriteria
{
    public function _pointApply($query, $repository)
    {
        $field = $this->getField();
        if (empty($field)) {
            return $query;
        }
        $value = $this->params['value'];
        $value = explode('|', $value);

        $query = $query->whereBetween($field, $value);

        return $query;
    }
}
