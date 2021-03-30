<?php



namespace App\Entity;



use App\Repository\ForumRepository;

use Doctrine\Common\Collections\ArrayCollection;

use Doctrine\Common\Collections\Collection;

use Doctrine\ORM\Mapping as ORM;



/**

 * @ORM\Entity(repositoryClass=ForumRepository::class)

 */

class Forum

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

    private $titre;



    /**

     * @ORM\Column(type="text", nullable=true)

     */

    private $contenu;



    /**

     * @ORM\Column(type="datetime", nullable=true)

     */

    private $create_at;



    /**

     * @ORM\ManyToOne(targetEntity=user::class, inversedBy="forums")

     */
    private $user;

    /**

     * @ORM\ManyToOne(targetEntity=Communaute::class, inversedBy="forums")

     */

    private $communaute;



    /**

     * @ORM\OneToMany(targetEntity=Commentaires::class, mappedBy="forum")

     */

    private $commentaires;



    /**

     * @ORM\OneToMany(targetEntity=ForumReponse::class, mappedBy="forum_id")

     */

    private $forumReponses;



    /**

     * @ORM\Column(type="boolean", nullable=true)

     */

    private $status;



    public function __construct()

    {

        $this->commentaires = new ArrayCollection();

        $this->forumReponses = new ArrayCollection();

    }



    public function getId(): ?int

    {

        return $this->id;

    }



    public function getTitre(): ?string

    {

        return $this->titre;

    }



    public function setTitre(string $titre): self

    {

        $this->titre = $titre;



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



    public function getCreateAt(): ?\DateTimeInterface

    {

        return $this->create_at;

    }



    public function setCreateAt(?\DateTimeInterface $create_at): self

    {

        $this->create_at = $create_at;



        return $this;

    }

    public function getUser(): ?user
    {
        return $this->user;
    }

    public function setUser(?user $user): self
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



    /**

     * @return Collection|Commentaires[]

     */

    public function getCommentaires(): Collection

    {

        return $this->commentaires;

    }



    public function addCommentaire(Commentaires $commentaire): self

    {

        if (!$this->commentaires->contains($commentaire)) {

            $this->commentaires[] = $commentaire;

            $commentaire->setForum($this);

        }



        return $this;

    }



    public function removeCommentaire(Commentaires $commentaire): self

    {

        if ($this->commentaires->removeElement($commentaire)) {

            // set the owning side to null (unless already changed)

            if ($commentaire->getForum() === $this) {

                $commentaire->setForum(null);

            }

        }



        return $this;

    }



    /**

     * @return Collection|ForumReponse[]

     */

    public function getForumReponses(): Collection

    {

        return $this->forumReponses;

    }



    public function addForumReponse(ForumReponse $forumReponse): self

    {

        if (!$this->forumReponses->contains($forumReponse)) {

            $this->forumReponses[] = $forumReponse;

            $forumReponse->setForumId($this);

        }



        return $this;

    }



    public function removeForumReponse(ForumReponse $forumReponse): self

    {

        if ($this->forumReponses->removeElement($forumReponse)) {

            // set the owning side to null (unless already changed)

            if ($forumReponse->getForumId() === $this) {

                $forumReponse->setForumId(null);

            }

        }



        return $this;

    }



     public function getStatus(): ?bool

    {

        return $this->status;

    }



    public function setStatus(?bool $status): self

    {

        $this->status = $status;



        return $this;

    }

}

