<?php

namespace App\Entity;

use App\Repository\CategoryRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=CategoryRepository::class)
 */
class Category
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
    private $nomCateg;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNomCateg(): ?string
    {
        return $this->nomCateg;
    }

    public function setNomCateg(string $nomCateg): self
    {
        $this->nomCateg = $nomCateg;

        return $this;
    }

	public function __toString()
	{
		return $this->getNomCateg();
	}


}
