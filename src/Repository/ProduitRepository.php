<?php

namespace App\Repository;

use App\Entity\Produit;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\EntityRepository;

/**
 * @method Produit|null find($id, $lockMode = null, $lockVersion = null)
 * @method Produit|null findOneBy(array $criteria, array $orderBy = null)
 * @method Produit[]    findAll()
 * @method Produit[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ProduitRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Produit::class);
    }

     /**
      * @return Produit[] Returns an array of Produit objects
     */
    public function findByTri_OR_AND_Categ($categ, $tri)
    {
	    $entityManager = $this->getEntityManager();

		if($tri == "decroissant" and $categ == ""){
				$query = $entityManager->createQuery(
					'SELECT p
            FROM App\Entity\Produit p
            ORDER BY p.prix DESC'
				);
		}
			else if($tri == "croissant" and $categ == ""){
				$query = $entityManager->createQuery(
					'SELECT p
            FROM App\Entity\Produit p
            ORDER BY p.prix ASC'
				);
			}
		else if($tri == "" and $categ != ""){
			$query = $entityManager->createQuery(
				'SELECT p
            FROM App\Entity\Produit p, App\Entity\Category c
            where p.category = c and c.nomCateg = :categ'
			)->setParameter('categ', $categ);
		}
		else if($tri != "" and $categ != ""){
			if($tri == "croissant"){
				$query = $entityManager->createQuery(
					'SELECT p
            FROM App\Entity\Produit p, App\Entity\Category c
            where p.category = c and c.nomCateg = :categ ORDER BY p.prix ASC'
				)->setParameter('categ', $categ);
			} else{
				$query = $entityManager->createQuery(
					'SELECT p
            FROM App\Entity\Produit p, App\Entity\Category c
            where p.category = c and c.nomCateg = :categ ORDER BY p.prix DESC'
				)->setParameter('categ', $categ);
			}

		}

	    return $query->getResult();
    }


    public function findSortAcceuil()
    {
	    $entityManager = $this->getEntityManager();

			    $query = $entityManager->createQuery(
				    'SELECT p
            FROM App\Entity\Produit p ORDER BY p.id DESC'
			    )->setMaxResults(3);

	    return $query->getResult();
    }

}
