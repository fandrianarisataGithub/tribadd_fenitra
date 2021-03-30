<?php

namespace App\Entity;

use App\Repository\MembresRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=MembresRepository::class)
 */
class Membres
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="membres")
     */
    private $user;

    /**
     * @ORM\ManyToOne(targetEntity=Communaute::class, inversedBy="membres")
     */
    private $communaute;

    /**
     * @ORM\Column(type="date", nullable=true)
     */
    private $dateDebut;

    /**
     * @ORM\Column(type="date", nullable=true)
     */
    private $dateFin;

    /**
     * @ORM\ManyToOne(targetEntity=Roles::class, inversedBy="membres")
     */
    private $Role;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $abonneNewsLetter;

    public function __construct()
    {
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getCommunaute(): ?Communaute
    {
        return $this->communaute;
    }

    public function setCommunaute(?Communaute $communaute): self
    {
        $this->communaute = $communaute;

        return $this;
    }

    public function getDateDebut(): ?\DateTimeInterface
    {
        return $this->dateDebut;
    }

    public function setDateDebut(?\DateTimeInterface $dateDebut): self
    {
        $this->dateDebut = $dateDebut;

        return $this;
    }

    public function getDateFin(): ?\DateTimeInterface
    {
        return $this->dateFin;
    }

    public function setDateFin(?\DateTimeInterface $dateFin): self
    {
        $this->dateFin = $dateFin;

        return $this;
    }

    public function getRole(): ?Roles
    {
        return $this->Role;
    }

    public function setRole(?Roles $Role): self
    {
        $this->Role = $Role;

        return $this;
    }

    public function getAbonneNewsLetter(): ?bool
    {
        return $this->abonneNewsLetter;
    }

    public function setAbonneNewsLetter(?bool $abonneNewsLetter): self
    {
        $this->abonneNewsLetter = $abonneNewsLetter;

        return $this;
    }
}
