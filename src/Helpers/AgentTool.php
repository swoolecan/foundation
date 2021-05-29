<?php

declare(strict_types = 1);

namespace Swoolecan\Foundation\Helpers;

use Jenssegers\Agent\Agent;

class AgentTool
{
	public static function isMobile()
	{
		return self::getIsElem('isMobile');
	}

	protected static function getIsElem($code)
	{
		$agent = new Agent();
		return $agent->$code();
	}
}
