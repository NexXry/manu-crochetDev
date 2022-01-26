<?php

namespace App\Entity;

use App\Repository\TrackerRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=TrackerRepository::class)
 */
class Tracker
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="bigint")
     */
    private $visiteTotal;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $dailyVisite;

    /**
     * @ORM\Column(type="bigint", nullable=true)
     */
    private $boutiqueVisite;

    /**
     * @ORM\Column(type="bigint", nullable=true)
     */
    private $nbUser;

    /**
     * @ORM\Column(type="bigint", nullable=true)
     */
    private $nbcommande;

    /**
     * @ORM\Column(type="date")
     */
    private $dateEnregistrement;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getVisiteTotal(): ?string
    {
        return $this->visiteTotal;
    }

    public function setVisiteTotal(string $visiteTotal): self
    {
        $this->visiteTotal = $visiteTotal;

        return $this;
    }

    public function getDailyVisite(): ?int
    {
        return $this->dailyVisite;
    }

    public function setDailyVisite(?int $dailyVisite): self
    {
        $this->dailyVisite = $dailyVisite;

        return $this;
    }

    public function getBoutiqueVisite(): ?string
    {
        return $this->boutiqueVisite;
    }

    public function setBoutiqueVisite(?string $boutiqueVisite): self
    {
        $this->boutiqueVisite = $boutiqueVisite;

        return $this;
    }

    public function getNbUser(): ?string
    {
        return $this->nbUser;
    }

    public function setNbUser(string $nbUser): self
    {
        $this->nbUser = $nbUser;

        return $this;
    }

    public function getNbcommande(): ?string
    {
        return $this->nbcommande;
    }

    public function setNbcommande(?string $nbcommande): self
    {
        $this->nbcommande = $nbcommande;

        return $this;
    }

    public function getDateEnregistrement(): ?\DateTimeInterface
    {
        return $this->dateEnregistrement;
    }

    public function setDateEnregistrement(\DateTimeInterface $dateEnregistrement): self
    {
        $this->dateEnregistrement = $dateEnregistrement;

        return $this;
    }
}
