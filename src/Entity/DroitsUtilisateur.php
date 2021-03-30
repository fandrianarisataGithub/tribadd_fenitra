<?php

namespace App\Entity;

use App\Repository\DroitsUtilisateurRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=DroitsUtilisateurRepository::class)
 */
class DroitsUtilisateur
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $creation;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $modification;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $visualisation;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $suppression;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $commentaire;

    /**
     * @ORM\OneToOne(targetEntity=Roles::class, inversedBy="droitsUtilisateur", cascade={"persist", "remove"})
     */
    private $role;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCreation(): ?bool
    {
        return $this->creation;
    }

    public function setCreation(?bool $creation): self
    {
        $this->creation = $creation;

        return $this;
    }

    public function getModification(): ?bool
    {
        return $this->modification;
    }

    public function setModification(?bool $modification): self
    {
        $this->modification = $modification;

        return $this;
    }

    public function getVisualisation(): ?bool
    {
        return $this->visualisation;
    }

    public function setVisualisation(?bool $visualisation): self
    {
        $this->visualisation = $visualisation;

        return $this;
    }

    public function getSuppression(): ?bool
    {
        return $this->suppression;
    }

    public function setSuppression(?bool $suppression): self
    {
        $this->suppression = $suppression;

        return $this;
    }

    public function getCommentaire(): ?bool
    {
        return $this->commentaire;
    }

    public function setCommentaire(?bool $commentaire): self
    {
        $this->commentaire = $commentaire;

        return $this;
    }

    public function getRole(): ?Roles
    {
        return $this->role;
    }

    public function setRole(?Roles $role): self
    {
        $this->role = $role;

        return $this;
    }
}
