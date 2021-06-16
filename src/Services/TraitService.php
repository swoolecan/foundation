<?php

declare(strict_types = 1);

namespace Swoolecan\Foundation\Services;

use Swoolecan\Foundation\Helpers\TraitResourceManager;

trait TraitService
{
    use TraitResourceManager;

    public function init()
    {
        if (!empty($this->pointRepository())) {
            $this->repository = $this->getRepositoryObj($this->pointRepository());
        }
    }

    protected function pointRepository()
    {
        return get_called_class();
    }

    public function __call($name, $arguments)
    {   
        return $this->repository->{$name}(...$arguments);
    }
}
