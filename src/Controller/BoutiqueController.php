<?php

namespace App\Controller;

use App\Entity\Category;
use App\Entity\Facture;
use App\Entity\LigneCommande;
use App\Entity\Produit;
use App\Event\TrackerBoutiqueVisitEvent;
use App\Event\TrackerIndexVisitEvent;
use App\Notification\MailNotification;
use App\Repository\CategoryRepository;
use App\Repository\FactureRepository;
use App\Repository\LigneCommandeRepository;
use App\Repository\ProduitRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\RequestStack;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Doctrine\Persistence\ManagerRegistry;

class BoutiqueController extends AbstractController
{
	private EventDispatcherInterface $eventDispatcher;
	/**
	 * @var RequestStack
	 */
	private $requestStack;

	public function __construct(EventDispatcherInterface $eventDispatcher,RequestStack $requestStack)
	{
		$this->eventDispatcher = $eventDispatcher;
		$this->requestStack = $requestStack;
	}






	/**
	 * Permet d'afficher tous les produits qui on était ajouté au site
	 *
	 * @param ProduitRepository $prod
	 * @param CategoryRepository $category
	 * @return Response
	 * @Route("/boutique", name="boutique")
	 */
    public function index(ProduitRepository $prod, CategoryRepository $category, LigneCommandeRepository $ligne): Response
    {

	    $event = new TrackerBoutiqueVisitEvent();
	    $this->eventDispatcher->dispatch($event);

		$pros= $prod->findAll();
		$produit=[];
	    foreach ($pros as $prodN) {
		    if($prodN->getQtt() > 0){
			    $produit[]=$prodN;
		    }
	    }

		$ligneCommande = $ligne->CountDem();
		if(isset($_POST['categ']) or isset($_POST['tri'])){
			$categ =htmlspecialchars($_POST['categ']);
			$tri = htmlspecialchars($_POST['tri']);
			if($categ != ""){
				$produits = $prod->findByTri_OR_AND_Categ($categ, $tri);
			} else if ($tri != ""){
				$produits = $prod->findByTri_OR_AND_Categ($categ, $tri);
			}
			else if ($tri != "" and $categ != ""){
				$produits = $prod->findByTri_OR_AND_Categ($categ, $tri);
			}
		} else {
			$produits = $produit;
		}

	    $prdDem = [];

	    foreach ($pros as $prodD) {
		    if($prodD->getQtt() == 0){
				$prdDem[]=$prodD;
		    }
		}

        return $this->render('boutique/index.html.twig', [
            'controller_name' => 'BoutiqueController',
	        "lesProds" => $produits,
	        "lesProdsDem" => $prdDem,
	        "category" => $category->findAll(),
	        "demCount"=>$ligneCommande
        ]);
    }


	/**
	 * Permet le focus sur un produit en particulier (Paramètre spécial : id = prod.id, qtt = la quantité de produit commander [1..5])
	 *
	 * @param ProduitRepository $prod
	 * @param integer $id
	 * @param integer $qtt
	 * @return Response
	 * @Route("/boutique/produit-{id}-{qtt}", name="prod")
	 */
	public function prod(ProduitRepository $prod,LigneCommandeRepository $ligne,$id,$qtt): Response
	{
		$produit= $prod->find($id);
		return $this->render('boutique/prod.html.twig', [
			'controller_name' => 'BoutiqueController',
			"prod" => $produit,
			"qtt"=>$qtt,
			"demCount"=>$ligne->CountDem()
		]);
	}


	/**
	 * Permet l'ajout au panier d'un produit et d'une quantité (fameux qtt passé dans l'url/paramètre)
	 * Produit sera stocker en session
	 * @Route("/boutique/panier-{id}-{qtt}/{dem}", name="prodToPanier")
	 * @IsGranted("ROLE_CLIENT")
	 * @param ProduitRepository $prod
	 * @param integer $id
	 * @param integer $qtt
	 * @return Response
	 */
	public function prodToPanier(ProduitRepository $prod,LigneCommandeRepository $ligne,$id,$qtt,int $dem): Response
	{
		$user = $this->get('security.token_storage')->getToken()->getUser();
		$session= $this->requestStack->getSession();
		$panier = $session->get("PanierClient");
		if(!isset($panier)){
			$session->set("PanierClient",[]);
		}

		if(!isset($panier)){
			$count=0;
		} else{
			$count=count($panier);
		}

		$panier[$count]["id"]=$id;
		$panier[$count]["qtt"]=$qtt;
		$panier[$count]["dem"]=$dem;

		$compt=["id"=>0,"nb"=>0,"dem"=>$dem];
		foreach ($panier as $prodPan) {
			if($prodPan["id"]==$id){
				$compt["id"]=$prodPan["id"];
				$compt["nb"]+=$prodPan["qtt"];
				//$compt["dem"]=$prodPan["dem"]=="1" ? true : false;
			}
		}

		if ($dem == 1){
			if ($user->isVerified() and $ligne->CountDem() < 15){
				if ($compt['id']==$id){
					$session->set('PanierClient', $panier);
					return $this->redirectToRoute('panier');
				}
			}
		} else{
			if ($user->isVerified()){
				if ($compt['id']==$id){
					$session->set('PanierClient', $panier);
					return $this->redirectToRoute('panier');
				}
			} else {
				$this->addFlash('err', "Vous ne pouvez commander que si vous avez confirmer votre mail !");

			}
		}


		return $this->redirectToRoute('prod', ['id'=>$id,"qtt"=>$qtt, "dem"=>$dem]);

	}

	/**
	 * Permet la suppression d'un produit dans le panier dans la session
	 * @param integer $id
	 * @Route("/boutique/del-{id}", name="delToPanier")
	 * @IsGranted("ROLE_CLIENT")
	 */
	public function delToPanier($id): Response
	{
		$session= $this->requestStack->getSession();
		$panier = $session->get("PanierClient");


		$panier = array_values($panier);
		unset($panier[$id]);
		$panier = array_values($panier);
		$session->set('PanierClient', $panier);

		return $this->redirectToRoute('panier');
	}

	/**
	 * Permet l'affichage du panier
	 * @Route("/panier", name="panier")
	 * @IsGranted("ROLE_CLIENT")
	 * @param ProduitRepository $prod
	 * @return Response
	 */
	public function panier(ProduitRepository $prod, LigneCommandeRepository $ligneCommande): Response
	{
		$session= $this->requestStack->getSession();
		$panier= $session->get('PanierClient');
		$produit=[];
		$sumQtt='';
		$subTab=[];
		if(!empty($panier)){
			$produit['sumQtt']=0;
			foreach ($panier as $prd) {
				$p=$prod->find($prd['id']);
				$subTab['prd']=$p;
				$subTab['qtt']=$prd['qtt'];
				$subTab['dem']=$prd['dem'];
				$subTab["prixhtTT"]=$prd['qtt']*$p->getPrix();
				$produit['sumQtt']+=$subTab['qtt'];
				$produit[]=$subTab;
			}
			$sumQtt=$produit["sumQtt"];
			unset($produit['sumQtt']);
		}


		return $this->render('boutique/panier.html.twig', [
			'controller_name' => 'BoutiqueController',
			"panier"=>$produit,
			"sumQtt"=>$sumQtt,
			"countDem"=>$ligneCommande->CountDem(),
		]);
	}


	/**
	 * Permet l'intégration de stripe afin de réaliser des paiements par carte bancaire
	 *
	 * @param ProduitRepository $prod
	 * @param float $fee
	 * @return \Symfony\Component\HttpFoundation\JsonResponse
	 * @Route("/create-checkout-session/{fee}", name="achat")
	 * @IsGranted("ROLE_CLIENT")
	 */
public function achat(ProduitRepository $prod,$fee)
{
	$user = $this->get('security.token_storage')->getToken()->getUser();
	$session= $this->requestStack->getSession();
	$panier= $session->get('PanierClient');

	$produit=[];
	$items=[];

	$subTab=[];
	if(!empty($panier)){
		foreach ($panier as $prd) {
			$p=$prod->find($prd['id']);
			$subTab['prd']=$p;
			$subTab['qtt']=$prd['qtt'];
			$subTab['dem']=$prd['dem'];
			$subTab["prixhtTT"]=$prd['qtt']*$p->getPrix();
			$produit[]=$subTab;
		}
	}

	$min =2;
	$max=10;

	foreach ($produit as $item) {

		if ($item['dem'] != "1"){
			$min =2;
			$max=10;
		} else{
			$min= 7;
			$max= 22;
		}


		$items[]=[
			'price_data' => [
				'currency' => 'eur',
				'product_data' => [
					'name' =>$item['prd']->getNomPrd(),
					'images' => [$this->getParameter('app.DOMAIN')."/images/".$item['prd']->getImages()[0]->getNom()],
				],
				'unit_amount' => round($item['prd']->getPrix()*100),
			],
			'quantity' => $item['qtt'],
		];
	}

	//Intégration de l'api stripe
	\Stripe\Stripe::setApiKey($this->getParameter('app.STRIPE'));

	$YOUR_DOMAIN = $this->getParameter('app.DOMAIN');

	$session = \Stripe\Checkout\Session::create([
		'customer_email' => $user->getEmail(),
		'submit_type' => 'pay',
		'billing_address_collection' => 'required',
		'shipping_address_collection' => [
			'allowed_countries' => ['FR'],
		],
		'shipping_options' => [
			[
				'shipping_rate_data' => [
					'type' => 'fixed_amount',
					'fixed_amount' => [
						'amount' =>  round($fee*100),
						'currency' => 'eur',
					],
					'display_name' => 'Livrer sous ',
					// Delivers between 5-7 business days
					'delivery_estimate' => [
						'minimum' => [
							'unit' => 'business_day',
							'value' => $min,
						],
						'maximum' => [
							'unit' => 'business_day',
							'value' => $max,
						],
					]
				]
			],
		],
		'payment_method_types' => ['card'],
		'line_items' => $items,
		'mode' => 'payment',
		'locale'=>"fr",
		'billing_address_collection'=> 'required',
		'success_url' => $this->generateUrl('success',['fee'=>$fee],UrlGeneratorInterface::ABSOLUTE_URL),
		'cancel_url' => $this->generateUrl('failed',[],UrlGeneratorInterface::ABSOLUTE_URL),
	]);

	//dd($session);
	return $this->json([ 'id' => $session->id ]);

}


	/**
	 * Permet de confirmer l'achat client et décompter le/les produits achetés
	 * @Route("/boutique/achat-succes-{fee}", name="success")
	 * @IsGranted("ROLE_CLIENT")
	 * @param ManagerRegistry $doctrine
	 * @param ProduitRepository $prod
	 * @param $fee
	 * @param MailNotification $mailer
	 * @return Response
	 */
public function sucesse(FactureRepository $facture,ManagerRegistry $doctrine,ProduitRepository $prod,LigneCommandeRepository $ligne,$fee,MailNotification $mailer): Response
{
	$user = $this->get('security.token_storage')->getToken()->getUser();
	$newFacture = new Facture();
	$entityManager = $doctrine->getManager();

	$session= $this->requestStack->getSession();
	$panier= $session->get("PanierClient");

	$produit=[];
	$subTab=[];
	$prixTT=0;
	if(!empty($panier)){
		foreach ($panier as $prd) {
			$p=$prod->find($prd['id']);
			$subTab['prd']=$p;
			$subTab['qtt']=$prd['qtt'];
			$subTab['dem']=$prd['dem'];
			$subTab["prixhtTT"]=$prd['qtt']*$p->getPrix();
			$subTab["fee"]=$fee;
			$produit[]=$subTab;
			$newLigne = new LigneCommande();
			$newLigne->setProduit($p);
			$newLigne->setCodeClient($user);
			$newLigne->setQtt($prd['qtt']);
			$newLigne->setDemande($prd['dem'] == "1" ? true : false);
			$newFacture->addListeProd($newLigne);
			$entityManager->persist($newFacture);

		}
	}

	foreach ($produit as $pro) {
		$prixTT+=$pro['prixhtTT'];
		if($pro['prd']->getQtt() > 0 and  $pro['prd']->getQtt() >= $pro['qtt']){
			$pro['prd']->setQtt($pro['prd']->getQtt()-$pro['qtt']);
			$entityManager->persist($pro['prd']);
		}
	}
	$newFacture->setTotalPrixPrd($prixTT);
	$newFacture->setPrixLivraison($fee);
	$newFacture->setClient($user);

	$entityManager->persist($newFacture);

	$entityManager->flush();

	$session->remove("PanierClient");

	$mailer->notifyNewAchat('Nouvelle commande sur Manu-crochet',$this->getParameter("app.MAILTOGO"),"nouvelle commande bouche !!!");

	return $this->redirectToRoute('app_profil',[]);
}

	/**
	 * Permet de rediriger le client si erreur de paiement.
	 * @Route("/boutique/achat-failed", name="failed")
	 * @IsGranted("ROLE_CLIENT")
	 * @return Response
	 */
	public function failed(): Response
	{
		return $this->render('boutique/failed.html.twig', [
			'controller_name' => 'BoutiqueController',
		]);
	}
}
