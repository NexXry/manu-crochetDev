<?php

namespace App\Controller;

use App\Entity\Devis;
use App\Form\DevisType;
use App\Form\PerduType;
use App\Form\ResetType;
use App\Notification\MailNotification;
use App\Repository\DevisRepository;
use App\Repository\LigneCommandeRepository;
use App\Repository\UserRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class ProfilController extends AbstractController
{
	/**
	 * Permet d'afficher le profil client (information principale)
	 * @param ManagerRegistry $doctrine
	 * @param DevisRepository $devisRepository
	 * @param Request $request
	 * @return Response
	 * @Route("/profil", name="app_profil")
	 */
	public function index(ManagerRegistry $doctrine, DevisRepository $devisRepository,Request $request): Response
	{
		$user = $this->get('security.token_storage')->getToken()->getUser();

		$devis = new Devis();
		$formC = $this->createForm(DevisType::class, $devis);
		$em = $doctrine->getManager();

		$formC->handleRequest($request);

		if ($formC->isSubmitted() && $formC->isValid()) {
			$devis->getMessageDevis($formC->getData());
			$devis->setEtat("en attente");
			$devis->setCodeClient($user);
			$em->persist($devis);
			$em->flush();
		}

		return $this->render('profil/index.html.twig', [
			'controller_name' => 'ProfilController',
			'commandes'=>$user->getFactureClient(),
			'form' => $formC->createView(),
			'devisClient' => $devisRepository->findAll(),
		]);
	}

	/**
	 * Permet la suppression de devis dans le profil client
	 * @param ManagerRegistry $doctrine
	 * @param DevisRepository $devisRepository
	 * @param $id
	 * @return Response
	 * @Route("/profil-devis-{id}", name="delDevis")
	 */
	public function delDevis(ManagerRegistry $doctrine, DevisRepository $devisRepository,$id): Response
	{
		$em = $doctrine->getManager();
		$em->remove($devisRepository->find($id));
		$em->flush();

		return $this->redirectToRoute("app_profil",[]);
	}

	/**
	 * Permet le focus sur une commande
	 * @param LigneCommandeRepository $ligneCommande
	 * @return Response
	 * @Route("/commande/{id}", name="commande")
	 */
	public function commande(LigneCommandeRepository $ligneCommande,$id): Response
	{
		$listeProd =$ligneCommande->findAll();
		$commande=[];
		$subTab=[];
		foreach ($listeProd as $prods) {
			foreach ($prods->getFactures() as $fact) {
				if ($fact->getId() == $id){
					$subTab['prd']=$prods->getProduit();
					$subTab['qtt']=$prods->getQtt();
					$subTab['dem']=$prods->getDemande();
					$commande[]=$subTab;
				}
			}
		}

		return $this->render('profil/commande.html.twig', [
			'controller_name' => 'ProfilController',
			'commande'=>$commande
		]);
	}

	/**
	 * Permet la récupération de mot de passe de façon sécuriser
	 *
	 * @Route("/perdu", name="app_perdu")
	 * @param Request $request
	 * @param UserRepository $user
	 * @param ManagerRegistry $doctrine
	 * @param MailNotification $mailer
	 * @return Response
	 */
	public function perdu(Request $request, UserRepository $user,ManagerRegistry $doctrine,MailNotification $mailer): Response
	{
		$form = $this->createForm(PerduType::class);
		$form->handleRequest($request);
		$entityManager = $doctrine->getManager();

		if ($form->isSubmitted() && $form->isValid()) {
			$mail = $form->getData()['mail'];
			$data = $user->findByMail($mail);
			$code = rand(0,9).rand(0,9).rand(0,9).rand(0,9).rand(0,9).rand(0,9);
			if(!empty($data)){
				$users = $user->find($data[0]);
				$users->setReset($code);
				$entityManager->persist($users);
				$entityManager->flush();
				$mailer->notifyContact('code de vérification', $mail, $code);

				return $this->redirectToRoute('app_resetMdp',['mail'=>$mail]);
			}

		}
		return $this->render('profil/perdu.html.twig', [
			'controller_name' => 'ProfilController',
			"formU"=>$form->createView(),
		]);
	}

	/**
	 * Permet la vérification du code du client envoyer par mail + modification du mot de passe
	 *
	 * @Route("/reset/{mail}", name="app_resetMdp")
	 * @param Request $request
	 * @param UserRepository $user
	 * @param Request
	 * @param ManagerRegistry $doctrine
	 * @param UserPasswordEncoderInterface $encoder
	 * @return Response
	 */
	public function reset(Request $request, UserRepository $user,$mail,ManagerRegistry $doctrine,UserPasswordEncoderInterface $encoder): Response
	{
		$entityManager = $doctrine->getManager();
		$form = $this->createForm(ResetType::class);
		$form->handleRequest($request);
		$err ="";
		if ($form->isSubmitted() && $form->isValid()) {
			$data = $user->findByMail($mail);
			$code = $form->getData()['code'];
			if(!empty($data)){
				$users =$user->findByMail($mail)[0]->getReset();
				if($users == $code){
					$users = $user->find($data[0]);
					$encoded = $encoder->encodePassword($users, $form->getData()['mdp']);
					$users->setPassword($encoded);
					$users->setReset("OULALA");
					$entityManager->persist($users);
					$entityManager->flush();
					return $this->redirectToRoute('app_login',[]);
				} else{
					$err="code invalide !";
				}
			}
		}

		return $this->render('profil/reset.html.twig', [
			'controller_name' => 'ProfilController',
			"formU"=>$form->createView(),
			"err"=>$err
		]);
	}

}
