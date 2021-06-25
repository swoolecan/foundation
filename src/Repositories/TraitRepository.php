<?php

declare(strict_types = 1);

namespace Swoolecan\Foundation\Repositories;
use Swoolecan\Foundation\Helpers\TraitResourceManager;

trait TraitRepository
{
    use TraitResourceManager;
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
}
