<?php

namespace App\EventSubscriber;

use App\Entity\Tracker;
use App\Event\TrackerIndexVisitEvent;
use App\Notification\MailNotification;
use DateTimeInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Validator\Constraints\DateTime;

class LoginTrackSubscriber implements EventSubscriberInterface
{
	private $_mailer;
	private $_manager;

	public function __construct(MailNotification $mailer,ManagerRegistry $manager)
	{
		$this->_mailer=$mailer;
		$this->_manager=$manager->getManager();
	}

    public function onVisitSuccessEvent(TrackerIndexVisitEvent $event)
    {
		$date =new \DateTime();
	    $track = $this->_manager->contains(new Tracker());
	    if(empty($track)){
		    $track = new Tracker();
		    $this->_mailer->notifyStats("manu.crochet.pro@gmail.com","stats",'nouvelle visite Index'
			    ,"Nouvelle stats du site");
	    } else {
		    $track = $this->_manager->getRepository(Tracker::class)->findAll()[array_key_last($this->_manager->getRepository(Tracker::class)->findAll())];
	    }
		if(empty($this->_manager->getRepository(Tracker::class)->findAll())){
			$track->setDailyVisite(1);
			$track->setVisiteTotal(1);
			$track->setDateEnregistrement($date);
			$this->_manager->persist($track);
			$this->_manager->flush();
		} else {
			$track = $this->_manager->getRepository(Tracker::class)->findAll()[array_key_last($this->_manager->getRepository(Tracker::class)->findAll())];
			$track->setDailyVisite($track->getDailyVisite()+1);
			$track->setVisiteTotal($track->getVisiteTotal()+1);
			$track->setDateEnregistrement($date);
			$dateComp = $this->_manager->getRepository(Tracker::class)->findAll()[array_key_last($this->_manager->getRepository(Tracker::class)->findAll())]->getDateEnregistrement();
			if ($date->format('Y/m/d') != $dateComp->format('Y/m/d')) {
				$this->_mailer->notifyStats("manu.crochet.pro@gmail.com","stats",'nouvelle visite Index'
					,"Nouvelle stats du site");
					$track = new Tracker();
					$track->setDailyVisite(1);
					$track->setVisiteTotal(1);
					$track->setBoutiqueVisite(null);
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
            TrackerIndexVisitEvent::class => 'onVisitSuccessEvent',
        ];
    }
}
