<?php 
declare(strict_types = 1);

namespace Swoolecan\Foundation\Criteria;

trait TraitSortCriteria
{
    public function _pointApply($query, $repository)
    {
        $this->params = empty($this->params) ? $repository->getDefaultSort() : $this->params;
        foreach ($this->params as $field => $sortType) {
            $sortType = in_array($sortType, ['asc', 'desc']) ? $sortType : 'desc';
            $query = $query->orderBy($field, $sortType);
        }

        return $query;
    }
}
