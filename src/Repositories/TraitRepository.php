<?php

declare(strict_types = 1);

namespace Swoolecan\Foundation\Repositories;

trait TraitRepository
{
    use DataTrait;
    use FieldTrait;
    use FormFieldTrait;
    use SearchFieldTrait;
    use ShowFieldTrait;
    use TreeTrait;

    /**
     * @var query
     */
    protected $query;

    /**
     * @var bool
     */
    protected $skipCriteria = false;

    /**
     * Prevents from overwriting same criteria in chain usage
     * @var bool
     */
    protected $preventCriteriaOverwriting = false;

    public function __call($name, $arguments)
    {   
        return $this->model->{$name}(...$arguments);
    }

    public function getCollectionClass($code = null)
    {
        return $this->resource->getClassName('collection', $code ? $code : get_called_class());
    }

    public function getResourceClass($code = null)
    {
        return $this->resource->getClassName('resource', $code ? $code : get_called_class());
    }
}
