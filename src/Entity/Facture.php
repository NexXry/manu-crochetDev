<?php

namespace App\Entity;

use App\Repository\FactureRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=FactureRepository::class)
 */
class Facture
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="float")
     */
    private $totalPrixPrd;

    /**
     * @ORM\Column(type="float")
     */
    private $prixLivraison;


    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $codeLivraison;

    /**
     * @ORM\ManyToMany(targetEntity=LigneCommande::class, inversedBy="factures",cascade={"persist"})
     */
    private $ListeProds;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="factureClient")
     * @ORM\JoinColumn(nullable=false)
     */
    private $client;

    public function __construct()
    {
        $this->ListeProds = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }


    public function getTotalPrixPrd(): ?float
    {
        return $this->totalPrixPrd;
    }

    public function setTotalPrixPrd(float $totalPrixPrd): self
    {
        $this->totalPrixPrd = $totalPrixPrd;

        return $this;
    }

    public function getPrixLivraison(): ?float
    {
        return $this->prixLivraison;
    }

    public function setPrixLivraison(float $prixLivraison): self
    {
        $this->prixLivraison = $prixLivraison;

        return $this;
    }

    public function getQttPrd(): ?int
    {
        return $this->qttPrd;
    }

    public function setQttPrd(int $qttPrd): self
    {
        $this->qttPrd = $qttPrd;

        return $this;
    }

    public function getCodeLivraison(): ?string
    {
        return $this->codeLivraison;
    }

    public function setCodeLivraison(string $codeLivraison): self
    {
        $this->codeLivraison = $codeLivraison;

        return $this;
    }

    /**
     * @return Collection|LigneCommande[]
     */
    public function getListeProds(): Collection
    {
        return $this->ListeProds;
    }

    public function addListeProd(LigneCommande $listeProd): self
    {
        if (!$this->ListeProds->contains($listeProd)) {
            $this->ListeProds[] = $listeProd;
        }

        return $this;
    }

    public function removeListeProd(LigneCommande $listeProd): self
    {
        $this->ListeProds->removeElement($listeProd);

        return $this;
    }

    public function getClient(): ?User
    {
        return $this->client;
    }

    public function setClient(?User $client): self
    {
        $this->client = $client;

        return $this;
    }
}
