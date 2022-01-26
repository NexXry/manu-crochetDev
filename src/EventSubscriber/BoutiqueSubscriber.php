<?php

namespace App\EventSubscriber;

use App\Entity\Tracker;
use App\Event\TrackerBoutiqueVisitEvent;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class BoutiqueSubscriber implements EventSubscriberInterface
{
	private $_manager;

	public function __construct(ManagerRegistry $manager)
	{
		$this->_manager=$manager->getManager();
	}

    public function onTrackerIndexVisitEvent(TrackerBoutiqueVisitEvent $event)
    {
	    $date =new \DateTime();
	    $track = $this->_manager->contains(new Tracker());

	    if(empty($track)){
		    $track = new Tracker();
	    } else {
		    $track = $this->_manager->getRepository(Tracker::class)->findAll()[array_key_last($this->_manager->getRepository(Tracker::class)->findAll())];
	    }
	    if(empty($this->_manager->getRepository(Tracker::class)->findAll())){
		    $track->setBoutiqueVisite(1);
		    $track->setVisiteTotal(1);
		    $track->setDateEnregistrement($date);
		    $this->_manager->persist($track);
		    $this->_manager->flush();
	    } else {
		    $track = $this->_manager->getRepository(Tracker::class)->findAll()[array_key_last($this->_manager->getRepository(Tracker::class)->findAll())];
		    $track->setBoutiqueVisite($track->getBoutiqueVisite()+1);
		    $track->setVisiteTotal($track->getVisiteTotal()+1);
		    $track->setDateEnregistrement($date);
		    $dateComp = $this->_manager->getRepository(Tracker::class)->findAll()[array_key_last($this->_manager->getRepository(Tracker::class)->findAll())]->getDateEnregistrement();
		    if ($date->format('Y/m/d') != $dateComp->format('Y/m/d')) {
			    $track = new Tracker();
			    $track->setBoutiqueVisite(1);
			    $track->setVisiteTotal(1);
			    $track->setDailyVisite(null);
			    $track->setNbcommande(null);
			    $track->setDateEnregistrement($date);
		    }
		    $this->_manager->persist($track);
		    $this->_manager->flush();
	    }
    }

    public static function getSubscribedEvents()
    {
        return [
	        TrackerBoutiqueVisitEvent::class => 'onTrackerIndexVisitEvent',
        ];
    }
}
