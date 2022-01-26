<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @ORM\Entity(repositoryClass=UserRepository::class)
 * @UniqueEntity(fields={"email"}, message="There is already an account with this email")
 */
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=180, unique=true)
     */
    private $email;

    /**
     * @ORM\Column(type="json")
     */
    private $roles = [];

    /**
     * @var string The hashed password
     * @ORM\Column(type="string")
     */
    private $password;

    /**
     * @ORM\Column(type="boolean")
     */
    private $isVerified = false;


	/**
	 * @ORM\Column(type="string")
	 */
	private $reset = "OULALA";

    /**
     * @ORM\OneToMany(targetEntity=Devis::class, mappedBy="codeClient")
     */
    private $devis;


	/**
	 * @ORM\Column(type="string")
	 */
	private $nom;

	/**
	 * @ORM\Column(type="string")
	 */
	private $prenom;

    /**
     * @ORM\OneToMany(targetEntity=Facture::class, mappedBy="client")
     */
    private $factureClient;


    public function __construct()
    {
        $this->devis = new ArrayCollection();
        $this->factures = new ArrayCollection();
        $this->factureClient = new ArrayCollection();
    }

	/**
	 * @return string
	 */
	public function getReset(): string
                                                                                                            	{
                                                                                                            		return $this->reset;
                                                                                                            	}

	/**
	 * @param string $reset
	 * @return User
	 */
	public function setReset(string $reset): User
                                                                                                            	{
                                                                                                            		$this->reset = $reset;
                                                                                                            		return $this;
                                                                                                            	}

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    /**
     * @deprecated since Symfony 5.3, use getUserIdentifier instead
     */
    public function getUsername(): string
    {
        return (string) $this->email;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
	    return array_unique($roles);
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    /**
     * Returning a salt is only needed, if you are not using a modern
     * hashing algorithm (e.g. bcrypt or sodium) in your security.yaml.
     *
     * @see UserInterface
     */
    public function getSalt(): ?string
    {
        return null;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials()
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    public function isVerified(): bool
    {
        return $this->isVerified;
    }

    public function setIsVerified(bool $isVerified): self
    {
        $this->isVerified = $isVerified;

        return $this;
    }

    /**
     * @return Collection|Devis[]
     */
    public function getDevis(): Collection
    {
        return $this->devis;
    }

    public function addDevi(Devis $devi): self
    {
        if (!$this->devis->contains($devi)) {
            $this->devis[] = $devi;
            $devi->setCodeClient($this);
        }

        return $this;
    }

    public function removeDevi(Devis $devi): self
    {
        if ($this->devis->removeElement($devi)) {
            // set the owning side to null (unless already changed)
            if ($devi->getCodeClient() === $this) {
                $devi->setCodeClient(null);
            }
        }

        return $this;
    }
	

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(string $nom): self
    {
        $this->nom = $nom;

        return $this;
    }

    public function getPrenom(): ?string
    {
        return $this->prenom;
    }

    public function setPrenom(string $prenom): self
    {
        $this->prenom = $prenom;

        return $this;
    }

    public function getAdresse(): ?string
    {
        return $this->adresse;
    }

    public function setAdresse(?string $Adresse): self
    {
        $this->adresse = $Adresse;

        return $this;
    }

    public function getCodePostal(): ?string
    {
        return $this->codePostal;
    }

    public function setCodePostal(?string $codePostal): self
    {
        $this->codePostal = $codePostal;

        return $this;
    }

    /**
     * @return Collection|Facture[]
     */
    public function getFactureClient(): Collection
    {
        return $this->factureClient;
    }

    public function addFactureClient(Facture $factureClient): self
    {
        if (!$this->factureClient->contains($factureClient)) {
            $this->factureClient[] = $factureClient;
            $factureClient->setClient($this);
        }

        return $this;
    }

    public function removeFactureClient(Facture $factureClient): self
    {
        if ($this->factureClient->removeElement($factureClient)) {
            // set the owning side to null (unless already changed)
            if ($factureClient->getClient() === $this) {
                $factureClient->setClient(null);
            }
        }

        return $this;
    }

	public function __toString()
	{
		return $this->getNom();
	}


}
