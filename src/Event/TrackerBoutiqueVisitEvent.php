<?php

namespace App\Event;

use Symfony\Contracts\EventDispatcher\Event;

class TrackerBoutiqueVisitEvent extends Event
{
	public const NAME = 'tracker.boutiqueVisit';

	public function __toString(): string
	{
		return sprintf('boutique was visits: %s', 1);
	}
}