<?php

namespace App\Notification;
use Twig\Environment;


class MailNotification
{
	private \Swift_Mailer  $mailer;
	/**
	 * @var Environment
	 */
	private Environment $renderer;

	public function __construct(\Swift_Mailer $mailer, Environment $renderer){

		$this->mailer = $mailer;
		$this->renderer = $renderer;
	}

	public function notifyContact($Subject,$userMail,$message){
		$message = (new \Swift_Message($Subject))
			->setFrom($_ENV['MAILTOGO'])
			->setTo($userMail)
			->setBody(
				$this->renderer->render(
				// templates/emails/registration.html.twig
					'components/mailMdp.html.twig',
					['message' => $message]
				),
				'text/html'
			)
		;
		$this->mailer->send($message);
	}

	public function notifyRefuse($Subject,$userMail,$message){
		$message = (new \Swift_Message($Subject))
			->setFrom($_ENV['MAILTOGO'])
			->setTo($userMail)
			->setBody(
				$this->renderer->render(
				// templates/emails/registration.html.twig
					'components/refuse.html.twig',
					['message' => $message]
				),
				'text/html'
			)
		;
		$this->mailer->send($message);
	}

	public function notifyAchat($Subject,$userMail,$message){
		$message = (new \Swift_Message($Subject))
			->setFrom($_ENV['MAILTOGO'])
			->setTo($userMail)
			->setBody(
				$this->renderer->render(
				// templates/emails/registration.html.twig
					'components/mail_achat.html.twig',
					['message' => $message]
				),
				'text/html'
			)
		;
		$this->mailer->send($message);
	}

	public function notifyNewAchat($Subject,$userMail,$message){
		$message = (new \Swift_Message($Subject))
			->setFrom($_ENV['MAILTOGO'])
			->setTo($userMail)
			->setBody(
				$this->renderer->render(
				// templates/emails/registration.html.twig
					'components/refuse.html.twig',
					['message' => $message]
				),
				'text/html'
			)
		;
		$this->mailer->send($message);
	}

	public function notifyExceptionPrd($userMail,$uri,$message,$trace){
		$message = (new \Swift_Message("C'est la merde !"))
			->setFrom($_ENV['MAILTOGO'])
			->setTo($userMail)
			->setBody(
				$this->renderer->render(
				// templates/emails/registration.html.twig
					'components/oula.html.twig',
					['uri' => $uri,'message' => $message,'trace' => $trace],
				),
				'text/html'
			)
		;
		$this->mailer->send($message);
	}

	public function notifyStats($userMail,$uri,$message,$trace){
		$message = (new \Swift_Message("C'est les Stats !"))
			->setFrom($_ENV['MAILTOGO'])
			->setTo($userMail)
			->setBody(
				$this->renderer->render(
				// templates/emails/registration.html.twig
					'components/oula.html.twig',
					['uri' => $uri,'message' => $message,'trace' => $trace],
				),
				'text/html'
			)
		;
		$this->mailer->send($message);
	}

}