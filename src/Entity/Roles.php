<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Repository\RolesRepository;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ORM\Entity(repositoryClass=RolesRepository::class)
 * @UniqueEntity("libelle", message="Le libellé éxiste déjà")
 */
class Roles
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=50)
     * @Assert\NotBlank(message="Ce champ est requis")
     */
    private $libelle;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $description;

    /**
     * @ORM\ManyToOne(targetEntity=Communaute::class, inversedBy="roles")
     */
    private $communaute;

    /**
     * @ORM\OneToOne(targetEntity=DroitsUtilisateur::class, mappedBy="role", cascade={"persist", "remove"})
     */
    private $droitsUtilisateur;

    /**
     * @ORM\OneToMany(targetEntity=Membres::class, mappedBy="Role")
     */
    private $membres;

    public function __construct()
    {
        $this->membres = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getLibelle(): ?string
    {
        return $this->libelle;
    }

    public function setLibelle(string $libelle): self
    {
        $this->libelle = $libelle;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getCommunaute(): ?Communaute
    {
        return $this->communaute;
    }

    public function setCommunaute(?Communaute $communaute): self
    {
        $this->communaute = $communaute;

        return $this;
    }

    public function getDroitsUtilisateur(): ?DroitsUtilisateur
    {
        return $this->droitsUtilisateur;
    }

    public function setDroitsUtilisateur(?DroitsUtilisateur $droitsUtilisateur): self
    {
        // unset the owning side of the relation if necessary
        if ($droitsUtilisateur === null && $this->droitsUtilisateur !== null) {
            $this->droitsUtilisateur->setRole(null);
        }

        // set the owning side of the relation if necessary
        if ($droitsUtilisateur !== null && $droitsUtilisateur->getRole() !== $this) {
            $droitsUtilisateur->setRole($this);
        }

        $this->droitsUtilisateur = $droitsUtilisateur;

        return $this;
    }

    /**
     * @return Collection|Membres[]
     */
    public function getMembres(): Collection
    {
        return $this->membres;
    }

    public function addMembre(Membres $membre): self
    {
        if (!$this->membres->contains($membre)) {
            $this->membres[] = $membre;
            $membre->setRole($this);
        }

        return $this;
    }

    public function removeMembre(Membres $membre): self
    {
        if ($this->membres->removeElement($membre)) {
            // set the owning side to null (unless already changed)
            if ($membre->getRole() === $this) {
                $membre->setRole(null);
            }
        }

        return $this;
    }
}
