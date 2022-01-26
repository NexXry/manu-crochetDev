<?php

namespace App\Controller;


use App\Event\TrackerIndexVisitEvent;
use App\EventListener\TrackIndexListener;
use App\EventSubscriber\LoginTrackSubscriber;
use App\Notification\MailNotification;
use App\Repository\ArticleRepository;
use App\Repository\ProduitRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Routing\Annotation\Route;
use App\Form\ContactType;

class IndexController extends AbstractController
{
	private EventDispatcherInterface $eventDispatcher;

	public function __construct(EventDispatcherInterface $eventDispatcher)
	{
		$this->eventDispatcher = $eventDispatcher;
	}


    /**
     * @Route("/", name="index")
     */
    public function index(ProduitRepository $prd,ArticleRepository $art,Request $request,MailNotification $mailer,ManagerRegistry $doctrine): Response
    {
	    $event = new TrackerIndexVisitEvent();
	    $this->eventDispatcher->dispatch($event);

        $form = $this->createForm(ContactType::class);

		$form->handleRequest($request);
		if ($form->isSubmitted()&& $form->isValid()){
			$mailer->notifyContact("Form Contact",$this->getParameter('app.MAILTOGO'),"{$form->get('nom')->getData()} / {$form->get('prenom')->getData()}
			Message : {$form->get('message')->getData()} / mail de la personne : {$form->get('mail')->getData()}
			");
		}

        return $this->render('index/index.html.twig', [
            'controller_name' => 'IndexController',
            'form' => $form->createView(),
	        "produits"=>$prd->findSortAcceuil(),
	        "articles"=>$art->findSortAcceuil()
        ]);
    }

    /**
     * @Route("/mention-légale", name="mention")
     */
    public function mention(): Response
    {

        return $this->render('index/mention.html.twig', [
            'controller_name' => 'IndexController',
        ]);
    }

	/**
	 * @Route("/CGU", name="condition")
	 */
	public function condis(): Response
	{

		return $this->render('index/condition.html.twig', [
			'controller_name' => 'IndexController',
		]);
	}

	/**
	 * @Route("/remboursement", name="reb")
	 */
	public function rebor(): Response
	{

		return $this->render('index/rb.html.twig', [
			'controller_name' => 'IndexController',
		]);
	}

	/**
	 * @Route("/articles", name="articles")
	 */
	public function articles( ArticleRepository $article): Response
	{

		return $this->render('index/articles.html.twig', [
			'controller_name' => 'IndexController',
			"articles"=>$article->findAll()
		]);
	}

	/**
	 * @Route("/article-{id}", name="article")
	 */
	public function article($id, ArticleRepository $article): Response
	{

		return $this->render('index/article.html.twig', [
			'controller_name' => 'IndexController',
			"article"=>$article->find($id)
		]);
	}



}
