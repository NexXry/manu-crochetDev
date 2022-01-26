<?php

namespace App\Event;

use Symfony\Contracts\EventDispatcher\Event;

class TrackerIndexVisitEvent extends Event
{
	public const NAME = 'tracker.indexVisit';

	public function __toString(): string
	{
		return sprintf('Index was visits: %s', 1);
	}
}