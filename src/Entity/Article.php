<?php

namespace App\Entity;

use App\Repository\ArticleRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=ArticleRepository::class)
 */
class Article
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $TitreArt;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $DescriptionArt;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $image;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitreArt(): ?string
    {
        return $this->TitreArt;
    }

    public function setTitreArt(string $TitreArt): self
    {
        $this->TitreArt = $TitreArt;

        return $this;
    }

    public function getDescriptionArt(): ?string
    {
        return $this->DescriptionArt;
    }

    public function setDescriptionArt(string $DescriptionArt): self
    {
        $this->DescriptionArt = $DescriptionArt;

        return $this;
    }

    public function getImage(): ?string
    {
        return $this->image;
    }

    public function setImage(string $image): self
    {
        $this->image = $image;

        return $this;
    }
}
