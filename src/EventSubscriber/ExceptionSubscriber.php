<?php

namespace App\EventSubscriber;

use App\Notification\MailNotification;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class ExceptionSubscriber implements EventSubscriberInterface
{
	private $_mailer;

	public function __construct(MailNotification $mailer)
	{
		$this->_mailer=$mailer;
	}

	public static function getSubscribedEvents()
    {
        return [
	        ExceptionEvent::class => 'onException',
        ];
    }

	public function onException(ExceptionEvent $event){
		$this->_mailer->notifyExceptionPrd("manu.crochet.pro@gmail.com",$event->getRequest()->getRequestUri(),$event->getThrowable()->getMessage()
		,$event->getThrowable()->getFile());
	}
}
