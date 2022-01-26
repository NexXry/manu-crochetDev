<?php

namespace App\Entity;

use App\Repository\DevisRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=DevisRepository::class)
 */
class Devis
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="devis")
     * @ORM\JoinColumn(nullable=false)
     */
    private $codeClient;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $messageDevis;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $etat;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCodeClient(): ?User
    {
        return $this->codeClient;
    }

    public function setCodeClient(?User $codeClient): self
    {
        $this->codeClient = $codeClient;

        return $this;
    }

    public function getMessageDevis(): ?string
    {
        return $this->messageDevis;
    }

    public function setMessageDevis(string $messageDevis): self
    {
        $this->messageDevis = $messageDevis;

        return $this;
    }

    public function getEtat(): ?string
    {
        return $this->etat;
    }

    public function setEtat(string $etat): self
    {
        $this->etat = $etat;

        return $this;
    }
}
