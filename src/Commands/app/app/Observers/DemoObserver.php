<?php

declare(strict_types = 1);

namespace Module{{UMODULE}}\Observers;

use Module{{UMODULE}}\Models\Demo;

class DemoObserver
{
    public function deleting(Demo $model)
    {
        //return $model->canDelete();
    }
}
