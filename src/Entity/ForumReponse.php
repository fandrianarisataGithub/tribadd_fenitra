<?php



namespace App\Entity;



use App\Repository\ForumReponseRepository;

use Doctrine\ORM\Mapping as ORM;



/**

 * @ORM\Entity(repositoryClass=ForumReponseRepository::class)

 */

class ForumReponse

{

    /**

     * @ORM\Id

     * @ORM\GeneratedValue

     * @ORM\Column(type="integer")

     */

    private $id;



    /**

     * @ORM\Column(type="text", nullable=true)

     */

    private $contenu;



    /**

     * @ORM\ManyToOne(targetEntity=user::class, inversedBy="forumReponses")

     */

    private $user;



    /**

     * @ORM\ManyToOne(targetEntity=forum::class, inversedBy="forumReponses")

     */

    private $forum;



    /**

     * @ORM\Column(type="datetime", nullable=true)

     */

    private $createAt;



    public function getId(): ?int

    {

        return $this->id;

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





    public function getUser(): ?User

    {

        return $this->user;

    }



    public function setUser(?User $user): self

    {

        $this->user = $user;



        return $this;

    }







    public function getForum(): ?forum

    {

        return $this->forum;

    }



    public function setForum(?forum $forum): self

    {

        $this->forum = $forum;



        return $this;

    }



    public function getCreateAt(): ?\DateTimeInterface

    {

        return $this->createAt;

    }



    public function setCreateAt(?\DateTimeInterface $createAt): self

    {

        $this->createAt = $createAt;



        return $this;

    }

}

