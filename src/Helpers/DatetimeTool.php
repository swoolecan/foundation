<?php

declare(strict_types = 1);

namespace Swoolecan\Foundation\Helpers;

use Carbon\Carbon;
use Overtrue\ChineseCalendar\Calendar;

class DatetimeTool
{
    static public function getCarbonObj($string = null)
    {
        if (is_null($string)) {
            return Carbon::now();
        }
        return Carbon::parse($string);
    }

    static public function calendar()
    {
        $calendar = new Calendar();
        $result = $calendar->solar(1881, 8, 3);
    }
}
