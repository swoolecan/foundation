<?php
declare(strict_types = 1);

namespace Swoolecan\Foundation\Criteria;

use Swoolecan\Foundation\Contracts\RepositoryInterface;

trait TraitRelateCriteria
{
    public function _pointApply($query, $repository)
    {
        $field = $this->getField();
        if (empty($field)) {
            return $query;
        }

        $params = $this->params;
        $query = $query->whereHasIn($params['elem'], function ($query) use ($field, $params) {
            $value = $params['operator'] == 'like' ? "%{$params['value']}%" : $params['value'];
            return $query->where($params['field'], $params['operator'], $value);
        });
        return $query;
    }
}
