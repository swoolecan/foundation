<?php
declare(strict_types = 1);

namespace Module{{UMODULE}}\Repositories;

use Framework\Baseapp\Repositories\AbstractRepository as AbstractRepositoryBase;

class AbstractRepository extends AbstractRepositoryBase
{
    protected function getAppcode()
    {
        return '{{MODULE}}';
    }
}
