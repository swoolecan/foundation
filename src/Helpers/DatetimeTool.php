<?php

declare(strict_types = 1);

namespace Swoolecan\Foundation\Helpers;

use Carbon\Carbon;

class DatetimeTool
{
    public function getNow()
    {
        return Carbon::now();
    }
    
    public function getCarbonObj($string)
    {
        return Carbon::parse($string);
    }
}
