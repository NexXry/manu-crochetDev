<?php

namespace App\Controller;

use App\Entity\Article;
use App\Entity\Category;
use App\Entity\Images;
use App\Form\ArticleType;
use App\Form\CategPrdType;
use App\Form\FactureType;
use App\Form\ImagesType;
use App\Notification\MailNotification;
use App\Repository\ArticleRepository;
use App\Repository\CategoryRepository;
use App\Repository\DevisRepository;
use App\Repository\FactureRepository;
use App\Repository\ImagesRepository;
use App\Repository\LigneCommandeRepository;
use App\Repository\ProduitRepository;
use App\Repository\UserRepository;
use Doctrine\Persistence\ManagerRegistry;
use App\Entity\Produit;
use App\Form\ProduitType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\Request;

class BackofficeController extends AbstractController
{
	/**
	 *  Index du backoffice permet l'affichage des informations principales (Nouvel achat client, factures, devis client)
	 * @Route("/admin", name="backoffice")
	 * @IsGranted("ROLE_CHEF", statusCode=404, message="La page n'existe pas.")
	 * @param FactureRepository $facture
	 * @return Response
	 */
    public function index(FactureRepository $facture): Response
    {
        return $this->render('backoffice/index.html.twig', [
            'controller_name' => 'BackofficeController',
	        'client'=>array_reverse($facture->findAll()),
        ]);
    }

	/**
	 *  Permet l'affiche des infos de la facture et l'ajoute du numéro de suivi + notifie le client par mail
	 * @Route("/admin/detail-{id}", name="detailComm")
	 * @IsGranted("ROLE_CHEF", statusCode=404, message="La page n'existe pas.")
	 * @param FactureRepository $facture
	 * @param $id
	 * @param ManagerRegistry $doctrine
	 * @param Request $request
	 * @param MailNotification $mailer
	 * @return Response
	 */
	public function detailComm(FactureRepository $facture, $id,ManagerRegistry $doctrine, Request $request,MailNotification $mailer): Response
	{

		$factu = $facture->find($id);
		$formC = $this->createForm(FactureType::class, $factu);
		$entityManager = $doctrine->getManager();

		$formC->handleRequest($request);
		if ($formC->isSubmitted() && $formC->isValid()) {
			$factu->setCodeLivraison($formC->get('codeLivraison')->getData());
			$entityManager->persist($factu);
			$entityManager->flush();
			$mailer->notifyAchat("Manu-Crochet Commande Expédier",$factu->getClient()->getEmail(),$formC->get('codeLivraison')->getData());
			return $this->redirectToRoute('backoffice');
		}

		return $this->render('backoffice/detailComm.html.twig', [
			'controller_name' => 'BackofficeController',
			'client'=>$facture->find($id),
			'formC' => $formC->createView(),
		]);
	}



	/**
	 *  Création de produit depuis un formulaire lié au backoffice, permet également la création de catégorie de produit
	 * (Un produit ne peut être créé sans catégorie. )
	 *
	 * @Route("/admin-gestionPrd", name="backoffice_prd")
	 * @IsGranted("ROLE_CHEF", statusCode=404, message="La page n'existe pas.")
	 * @param ManagerRegistry $doctrine
	 * @param Request $request
	 * @param CategoryRepository $lesCateg
	 * @param ProduitRepository $lesProds
	 * @param ImagesRepository $images
	 * @return Response
	 */
	public function prd(ManagerRegistry $doctrine,Request $request, CategoryRepository $lesCateg, ProduitRepository $lesProds,ImagesRepository $images): Response
	{
		$prod = new Produit();
		$form = $this->createForm(ProduitType::class, $prod);

		$categ = new Category();
		$formC = $this->createForm(CategPrdType::class, $categ);

		$imgs = new Images();
		$formI = $this->createForm(ImagesType::class);
		$entityManager = $doctrine->getManager();


		$formC->handleRequest($request);
		$form->handleRequest($request);
		$formI->handleRequest($request);

		if ($formC->isSubmitted() && $formC->isValid()) {
			$categ = $formC->getData();
			$entityManager->persist($categ);
			$entityManager->flush();
			return $this->redirectToRoute('backoffice_prd');
		}

		if ($form->isSubmitted() && $form->isValid()) {

			$images = $form->get('images')->getData();

			// On boucle sur les images
			foreach($images as $image){
				// On génère un nouveau nom de fichier
				$fichier = md5(uniqid()).'.'.$image->guessExtension();

				// On copie le fichier dans le dossier uploads
				$image->move(
					$this->getParameter('images_directory'),
					$fichier
				);

				// On crée l'image dans la base de données
				$img = new Images();
				$img->setNom($fichier);
				$prod->setNomPrd($form->get('nomPrd')->getData());
				$prod->setSousTitre($form->get('sousTitre')->getData());
				$prod->setCategory($form->get('category')->getData());
				$prod->setPrix($form->get('prix')->getData());
				$prod->setQtt($form->get('qtt')->getData());
				$prod->setArchiver(0);
				$prod->setDescription($form->get('description')->getData());
				$prod->addImage($img);
			}

			$entityManager = $this->getDoctrine()->getManager();
			$entityManager->persist($img);
			$entityManager->persist($prod);
			$entityManager->flush();
			return $this->redirectToRoute('backoffice_prd');
		}

		if ($formI->isSubmitted() && $formI->isValid()) {

			$images = $formI->get('images')->getData();
			$prdLiers = $lesProds->find($formI->get('Produit')->getData()->getId());
			// On boucle sur les images
			foreach($images as $image){
				// On génère un nouveau nom de fichier
				$fichier = md5(uniqid()).'.'.$image->guessExtension();

				// On copie le fichier dans le dossier uploads
				$image->move(
					$this->getParameter('images_directory'),
					$fichier
				);

				// On crée l'image dans la base de données
				$imgs->setNom($fichier);
				$prdLiers->addImage($imgs);
			}

			$entityManager = $this->getDoctrine()->getManager();
			$entityManager->persist($imgs);
			$entityManager->persist($prdLiers);
			$entityManager->flush();
			return $this->redirectToRoute("backoffice_prd");

		}

		return $this->render('backoffice/GestionPrd.html.twig', [
			'controller_name' => 'BackofficeController',
			'form' => $form->createView(),
			'formC' => $formC->createView(),
			'formI' => $formI->createView(),
			'lesCateg' => $lesCateg->findAll(),
			'lesProds' => $lesProds->findAll(),
			'images' => $images->findAll(),
		]);
	}

	/**
	 *  Permet la suppression de catégorie
	 *
	 * @Route("/admin-arch-{id}", name="backoffice_Archiver")
	 * @IsGranted("ROLE_CHEF", statusCode=404, message="La page n'existe pas.")
	 */
	public function backoffice_Archiver($id,ManagerRegistry $doctrine, ProduitRepository $prod): Response
	{
		$entityManager = $doctrine->getManager();
		$entityManager->persist($prod->find($id)->setArchiver(1));
		$entityManager->flush();
		return $this->redirectToRoute('backoffice_prd');
	}

	/**
	 *  Permet la suppression de catégorie
	 *
	 * @Route("/admin-del-categorie-{id}", name="backoffice_delCateg")
	 * @IsGranted("ROLE_CHEF", statusCode=404, message="La page n'existe pas.")
	 */
	public function delCateg($id,ManagerRegistry $doctrine, CategoryRepository $lesCateg,ProduitRepository $prod): Response
	{
		$entityManager = $doctrine->getManager();

		if($prod->findAll() == []){
			$entityManager->remove($lesCateg->find($id));
		} else{
		foreach ($prod->findAll() as $produit) {
			if($lesCateg->find($id) != $produit->getCategory()){
				$entityManager->remove($lesCateg->find($id));
			} else{
				return $this->redirectToRoute('backoffice_prd');
			}
		}
		}
		$entityManager->flush();
		return $this->redirectToRoute('backoffice_prd');
	}

	/**
	 *  Permet la suppression d'image lier à un produit (Car une image ou plusieurs images peuvent appartenir à un produit)
	 *
	 * @Route("/admin-del-image-{id}", name="backoffice_delImg")
	 * @IsGranted("ROLE_CHEF", statusCode=404, message="La page n'existe pas.")
	 */
	public function delImg($id,ManagerRegistry $doctrine,Request $request, ImagesRepository $lesImg,ProduitRepository $prod): Response
	{
		$img = $lesImg->find($id);
		foreach ($prod->findAll() as $produit) {
			foreach ($produit->getImages() as $image) {
				if($img != $image){
					$entityManager = $doctrine->getManager();
				} else{
					return $this->redirectToRoute('backoffice_prd');
				}
			}
		};
		$pathToFile = $this->getParameter('images_directory').'/'.$img->getNom();
		if (file_exists($pathToFile)) {
			unlink($pathToFile);
		}
		$entityManager->remove($img);
		$entityManager->flush();
		return $this->redirectToRoute('backoffice_prd');
	}

	/**
	 *  Permet la suppression de produit
	 *
	 * @Route("/admin-del-produit-{id}", name="backoffice_delProd")
	 * @IsGranted("ROLE_CHEF", statusCode=404, message="La page n'existe pas.")
	 */
	public function delProd($id,ManagerRegistry $doctrine,Request $request, ProduitRepository $lesPrds,ImagesRepository $lesImg,LigneCommandeRepository $ligne): Response
	{
		$entityManager = $doctrine->getManager();
		$prod =$lesPrds->find($id);
		foreach ($ligne->findAll() as $ligneCommande) {
			if($ligneCommande->getProduit()==$prod){
				$this->addFlash('err', "le produit est liés a une commande !");
				return $this->redirectToRoute('backoffice_prd');
			}
		}
		try{
				foreach ($lesPrds->find($id)->getImages() as $image) {
					$img = $lesImg->find($image->getId());
					$pathToFile = $this->getParameter('images_directory').'/'.$img->getNom();
					if (file_exists($pathToFile)) {
						unlink($pathToFile);
					}
					$entityManager->remove($img);
					$entityManager->flush();
				}
				$entityManager->remove($lesPrds->find($id));
				$entityManager->flush();
		}catch (\Exception $e){
			$this->addFlash('err', "Vous ne pouvez pas retirer le produit !");
		}


		return $this->redirectToRoute('backoffice_prd');
	}



	/**
	 *  Permet l'ajout d'article sur le site.
	 *
	 * @Route("/admin-articles", name="backoffice_article")
	 * @IsGranted("ROLE_CHEF", statusCode=404, message="La page n'existe pas.")
	 * @param ArticleRepository $art
	 * @param ManagerRegistry $doctrine
	 * @param Request $request
	 * @return Response
	 */
	public function articles(ArticleRepository $art,ManagerRegistry $doctrine,Request $request): Response
	{
		$article = new Article();
		$form = $this->createForm(ArticleType::class, $article);

		$imgs = new Images();

		$form->handleRequest($request);

		if ($form->isSubmitted() && $form->isValid()) {
			$images = $form->get('image')->getData();
			// On boucle sur les images

			foreach ($images as $image) {


				// On génère un nouveau nom de fichier
				$fichier = md5(uniqid()).'.'.$image->guessExtension();

				// On copie le fichier dans le dossier uploads
				$image->move(
					$this->getParameter('images_directory'),
					$fichier
				);
			}

				// On crée l'image dans la base de données
				$imgs->setNom($fichier);
				$article->setImage($imgs);

			$entityManager = $this->getDoctrine()->getManager();
			$entityManager->persist($imgs);
			$entityManager->persist($article);
			$entityManager->flush();

			return $this->redirectToRoute('backoffice_article');
		}


		return $this->render('backoffice/GestionArticles.html.twig', [
			'controller_name' => 'BackofficeController',
			"form"=>$form->createView(),
			"Articles"=>$art->findAll()
		]);
	}


	/**
	 *  Permet la suppression d'article
	 *
	 * @Route("/admin-del-article-{id}", name="backoffice_delArt")
	 * @IsGranted("ROLE_CHEF", statusCode=404, message="La page n'existe pas.")
	 */
	public function delArt($id,ManagerRegistry $doctrine,Request $request, ArticleRepository $art): Response
	{
		$entityManager = $doctrine->getManager();
		$entityManager->remove($art->find($id));

			$image=$art->find($id);
			$article = $art->find($image->getId());
			$pathToFile = $this->getParameter('images_directory').'/'.$article->getImage();
			if (file_exists($pathToFile)) {
				unlink($pathToFile);
			}
			$entityManager->remove($article);
			$entityManager->flush();

		$entityManager->flush();
		return $this->redirectToRoute('backoffice_article');
	}


	/**
	 * Permet la gestion des devis client
	 * @Route("/admin-Devis", name="backoffice_devis")
	 * @IsGranted("ROLE_CHEF", statusCode=404, message="La page n'existe pas.")
	 */
	public function Devis(DevisRepository $devis): Response
	{
		return $this->render('backoffice/GestionDevis.html.twig', [
			'controller_name' => 'BackofficeController',
			"devis"=>$devis->findAll(),
		]);
	}

	/**
	 * Permet la suppression des devis client
	 * @Route("/admin-DevisAc-{id}", name="backoffice_devis_accDevis")
	 * @IsGranted("ROLE_CHEF", statusCode=404, message="La page n'existe pas.")
	 * @param DevisRepository $devis
	 * @param ManagerRegistry $doctrine
	 * @return Response
	 */
	public function accDevis(DevisRepository $devis,$id,ManagerRegistry $doctrine,MailNotification $mailer): Response
	{
		$em=$doctrine->getManager();
		$mailer->notifyRefuse("Votre demande sur manu-crochet est accepter",$devis->find($id)->getCodeClient()->getEmail(),"Veuillez me contactez au 06 78 23 64 60 pour définir un tarif.");
		$devis->find($id)->setEtat("accepter");
		$em->persist($devis->find($id));
		$em->flush();
		return $this->redirectToRoute('backoffice_devis',[]);
	}

	/**
	 * Permet la suppression des devis client
	 * @Route("/admin-Devis-{id}", name="backoffice_devis_delDevis")
	 * @IsGranted("ROLE_CHEF", statusCode=404, message="La page n'existe pas.")
	 * @param DevisRepository $devis
	 * @param ManagerRegistry $doctrine
	 * @return Response
	 */
	public function delDevis(DevisRepository $devis,$id,ManagerRegistry $doctrine,MailNotification $mailer): Response
	{
		$motif=htmlspecialchars($_POST['motif']);
		$em=$doctrine->getManager();
		$mailer->notifyRefuse("Votre demande sur manu-crochet est refuser",$devis->find($id)->getCodeClient()->getEmail(),"Motif du refus : ".$motif);
		$em->remove($devis->find($id));
		$em->flush();
		return $this->redirectToRoute('backoffice_devis',[]);
	}

}
