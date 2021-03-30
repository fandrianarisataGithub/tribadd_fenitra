<?php

namespace App\Entity;

use App\Repository\PointUserRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=PointUserRepository::class)
 */
class PointUser
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=Communaute::class, inversedBy="pointUsers")
     */
    private $communaute;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="pointUsers")
     */
    private $user;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $valeur;

    public function getId(): ?int
    {
        return $this->id;
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

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function getValeur(): ?int
    {
        return $this->valeur;
    }

    public function setValeur(?int $valeur): self
    {
        $this->valeur = $valeur;

        return $this;
    }
}
