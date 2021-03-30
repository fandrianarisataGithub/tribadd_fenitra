<?php

namespace App\Entity;

use App\Repository\CommentairesRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=CommentairesRepository::class)
 */
class Commentaires
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $commentedAt;

    /**
     * @ORM\ManyToOne(targetEntity=Post::class, inversedBy="commentaires")
     */
    private $post;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="commentaires")
     */
    private $user;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $contenus;

    /**
     * @ORM\ManyToOne(targetEntity=Article::class, inversedBy="commentaires")
     */
    private $article;

    /**
     * @ORM\ManyToOne(targetEntity=Video::class, inversedBy="commentaires")
     */
    private $video;

    /**
     * @ORM\ManyToOne(targetEntity=Forum::class, inversedBy="commentaires")
     */
    private $forum;

    /**
     * @ORM\OneToMany(targetEntity=Commentaires::class, mappedBy="reponses")
     */
    private $parent;

    public function __construct()
    {
        $this->parent = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCommentedAt(): ?\DateTimeInterface
    {
        return $this->commentedAt;
    }

    public function setCommentedAt(?\DateTimeInterface $commentedAt): self
    {
        $this->commentedAt = $commentedAt;

        return $this;
    }

    public function getPost(): ?Post
    {
        return $this->post;
    }

    public function setPost(?Post $post): self
    {
        $this->post = $post;

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

    public function getContenus(): ?string
    {
        return $this->contenus;
    }

    public function setContenus(?string $contenus): self
    {
        $this->contenus = $contenus;

        return $this;
    }

    public function getArticle(): ?Article
    {
        return $this->article;
    }

    public function setArticle(?Article $article): self
    {
        $this->article = $article;

        return $this;
    }

    public function getVideo(): ?Video
    {
        return $this->video;
    }

    public function setVideo(?Video $video): self
    {
        $this->video = $video;

        return $this;
    }

    public function getForum(): ?Forum
    {
        return $this->forum;
    }

    public function setForum(?Forum $forum): self
    {
        $this->forum = $forum;

        return $this;
    }

    /**
     * @return Collection|self[]
     */
    public function getParent(): Collection
    {
        return $this->parent;
    }

    public function addParent(self $parent): self
    {
        if (!$this->parent->contains($parent)) {
            $this->parent[] = $parent;
            $parent->setReponses($this);
        }

        return $this;
    }
    public function removeParent(self $parent): self
    {
        if ($this->parent->removeElement($parent)) {
            // set the owning side to null (unless already changed)
            if ($parent->getReponses() === $this) {
                $parent->setReponses(null);
            }
        }

        return $this;
    }
}
