<?php

namespace App\Entity;

use App\Repository\PostRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=PostRepository::class)
 */
class Post
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $titre;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $sous_titre;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $image_encadre;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $image_fond;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $contenus;

    /**
     * @ORM\ManyToOne(targetEntity=Communaute::class, inversedBy="post")
     */
    private $communaite;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="posts")
     */
    private $user;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitre(): ?string
    {
        return $this->titre;
    }

    public function setTitre(?string $titre): self
    {
        $this->titre = $titre;

        return $this;
    }

    public function getSousTitre(): ?string
    {
        return $this->sous_titre;
    }

    public function setSousTitre(?string $sous_titre): self
    {
        $this->sous_titre = $sous_titre;

        return $this;
    }

    public function getImageEncadre(): ?string
    {
        return $this->image_encadre;
    }

    public function setImageEncadre(?string $image_encadre): self
    {
        $this->image_encadre = $image_encadre;

        return $this;
    }

    public function getImageFond(): ?string
    {
        return $this->image_fond;
    }

    public function setImageFond(?string $image_fond): self
    {
        $this->image_fond = $image_fond;

        return $this;
    }

    public function getContenus(): ?string
    {
        return $this->contenus;
    }

    public function setContenus(?string $contenus): self
    {
        $this->contenus = $contenus;

        return $this;
    }

    public function getComminaute(): ?Communaute
    {
        return $this->communaite;
    }

    public function setComminaute(?Communaute $comminaute): self
    {
        $this->communaite = $comminaute;

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
}
