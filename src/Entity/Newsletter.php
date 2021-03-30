<?php

namespace App\Entity;

use App\Repository\NewsletterRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=NewsletterRepository::class)
 */
class Newsletter
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=Communaute::class, inversedBy="newsletters")
     */
    private $communaute;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $object;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $contenu;

    /**
     * @ORM\Column(type="json", nullable=true)
     */
    private $idPosts = [];

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $isSended;

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

    public function getObject(): ?string
    {
        return $this->object;
    }

    public function setObject(?string $object): self
    {
        $this->object = $object;

        return $this;
    }

    public function getContenu(): ?string
    {
        return $this->contenu;
    }

    public function setContenu(?string $contenu): self
    {
        $this->contenu = $contenu;

        return $this;
    }

    public function getIdPosts(): ?array
    {
        return $this->idPosts;
    }

    public function setIdPosts(?array $idPosts): self
    {
        $this->idPosts = $idPosts;

        return $this;
    }

    public function getIsSended(): ?bool
    {
        return $this->isSended;
    }

    public function setIsSended(?bool $isSended): self
    {
        $this->isSended = $isSended;

        return $this;
    }
}
