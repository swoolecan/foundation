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
        $query = $query->whereHas($params['elem'], function ($query) use ($field, $params) {
            $query->where($params['field'], $params['operator'], "%{$params['value']}");
        });

        return $query;
    }
}
