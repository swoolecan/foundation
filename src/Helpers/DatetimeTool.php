<?php

declare(strict_types = 1);

namespace Swoolecan\Foundation\Helpers;

use Carbon\Carbon;

class DatetimeTool
{
    static public function getCarbonObj($string = null)
    {
        if (is_null($string)) {
            return Carbon::now();
        }
        return Carbon::parse($string);
    }
}
