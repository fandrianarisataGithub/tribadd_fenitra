<?php



namespace App\Entity;



use Doctrine\ORM\Mapping as ORM;

use App\Repository\UserRepository;

use Doctrine\Common\Collections\Collection;

use Doctrine\Common\Collections\ArrayCollection;

use Symfony\Component\Validator\Constraints as Assert;

use Symfony\Component\Security\Core\User\UserInterface;

use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;



/**

 * @ORM\Entity(repositoryClass=UserRepository::class)

 * @UniqueEntity("email", message="Cette adresse email existe déjà")

 * @UniqueEntity("username", message="Ce pseudo email existe déjà")

 * @ORM\HasLifecycleCallbacks()

 */

class User implements UserInterface

{

    /**

     * @ORM\Id

     * @ORM\GeneratedValue

     * @ORM\Column(type="integer")

     */

    private $id;



    /**

     * @ORM\Column(type="string", length=100, nullable=true)

     * @Assert\NotBlank(message="Ce champ est requis")

     */

    private $username;



    /**

     * @ORM\Column(type="string", length=100, nullable=true)

     */

    private $password;



    /**

     * @ORM\Column(type="string", length=100, nullable=true)

     */

    private $nom;



    /**

     * @ORM\Column(type="string", length=100, nullable=true)

     */

    private $prenom;



    /**

     * @ORM\Column(type="string", length=100, nullable=true)

     */

    private $adresse;



    /**

     * @ORM\Column(name="email", type="string", length=100, nullable=true)

     * @Assert\Email(

     *     message="Veuillez insérer une adresse email valide"

     * )

     * @Assert\NotBlank(message="Ce champ est requis")

     */

    private $email;



    /**

     * @ORM\Column(type="json", nullable=true)

     */

    private $roles = [];



    /**

     * @ORM\Column(type="boolean", nullable=true)

     */

    private $status;



    /**

     * @ORM\Column(type="datetime", nullable=true)

     */

    private $creation;



    /**

     * @ORM\Column(type="date", nullable=true)

     */

    private $dateNaissance;



    /**

     * @ORM\Column(type="text", nullable=true)

     */

    private $description;



    /**

     * @ORM\Column(type="string", length=255, nullable=true)

     */

    private $siteWeb;



    /**

     * @ORM\Column(type="string", length=255, nullable=true)

     */

    private $telephone;



    /**

     * @ORM\Column(type="string", length=255, nullable=true)

     */

    private $photo;



    /**

     * @ORM\OneToMany(targetEntity=Communaute::class, mappedBy="user")

     */

    private $communautes;



    /**

     * @ORM\OneToMany(targetEntity=Membres::class, mappedBy="user")

     */

    private $membres;



    /**

     * @ORM\OneToMany(targetEntity=Post::class, mappedBy="user")

     */

    private $posts;



    /**

     * @ORM\OneToMany(targetEntity=Commentaires::class, mappedBy="user")

     */

    private $commentaires;



    /**

     * @ORM\OneToMany(targetEntity=PointUser::class, mappedBy="user")

     */

    private $pointUsers;



    /**

     * @ORM\OneToMany(targetEntity=Article::class, mappedBy="user")

     */

    private $articles;



    /**

     * @ORM\OneToMany(targetEntity=Chat::class, mappedBy="user")

     */

    private $chats;



    /**

     * @ORM\OneToMany(targetEntity=Message::class, mappedBy="user")

     */

    private $messages;



    /**

     * @ORM\OneToMany(targetEntity=Like::class, mappedBy="user")

     */

    private $likes;



    /**

     * @ORM\OneToMany(targetEntity=Video::class, mappedBy="user")

     */

    private $videos;



    /**

     * @ORM\OneToMany(targetEntity=DownloadSpace::class, mappedBy="user")

     */

    private $files;



    /**

     * @ORM\OneToMany(targetEntity=Forum::class, mappedBy="user")

     */

    private $forums;



    /**

     * @ORM\OneToMany(targetEntity=ForumReponse::class, mappedBy="user_id")

     */

    private $forumReponses;

    /**
     * @ORM\OneToMany(targetEntity=Event::class, mappedBy="user")
     */
    private $events;



    public function __construct()

    {

        $this->posts = new ArrayCollection();

        $this->communautes = new ArrayCollection();

        $this->membres = new ArrayCollection();

        $this->commentaires = new ArrayCollection();

        $this->pointUsers = new ArrayCollection();

        $this->articles = new ArrayCollection();

        $this->chats = new ArrayCollection();

        $this->messages = new ArrayCollection();

        $this->likes = new ArrayCollection();

        $this->videos = new ArrayCollection();

        $this->files = new ArrayCollection();

        $this->forums = new ArrayCollection();

        $this->forumReponses = new ArrayCollection();
        $this->events = new ArrayCollection();

    }



    public function getId(): ?int

    {

        return $this->id;

    }



    public function getUsername(): ?string

    {

        return $this->username;

    }



    public function setUsername(?string $username): self

    {

        $this->username = $username;



        return $this;

    }



    public function getPassword(): ?string

    {

        return $this->password;

    }



    public function setPassword(?string $password): self

    {

        $this->password = $password;



        return $this;

    }



    public function getNom(): ?string

    {

        return $this->nom;

    }



    public function setNom(?string $nom): self

    {

        $this->nom = $nom;



        return $this;

    }



    public function getPrenom(): ?string

    {

        return $this->prenom;

    }



    public function setPrenom(?string $prenom): self

    {

        $this->prenom = $prenom;



        return $this;

    }



    public function getAdresse(): ?string

    {

        return $this->adresse;

    }



    public function setAdresse(?string $adresse): self

    {

        $this->adresse = $adresse;



        return $this;

    }



    public function getEmail(): ?string

    {

        return $this->email;

    }



    public function setEmail(?string $email): self

    {

        $this->email = $email;



        return $this;

    }



    public function setRoles(?array $roles): self

    {

        $this->roles = $roles;



        return $this;

    }



    /**

     * Returns the roles granted to the user.

     *

     *     public function getRoles()

     *     {

     *         return ['ROLE_USER'];

     *     }

     *

     * Alternatively, the roles might be stored on a ``roles`` property,

     * and populated in any number of different ways when the user object

     * is created.

     *

     * @return void (Role|string)[] The user roles

     */

    public function getRoles()

    {

        return $this->roles;

    }



    /**

     * Returns the salt that was originally used to encode the password.

     *

     * This can return null if the password was not encoded using a salt.

     *

     * @return string|null The salt

     */

    public function getSalt()

    {

        // TODO: Implement getSalt() method.

    }



    /**

     * Removes sensitive data from the user.

     *

     * This is important if, at any given point, sensitive information like

     * the plain-text password is stored on this object.

     */

    public function eraseCredentials()

    {

        // TODO: Implement eraseCredentials() method.

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



    public function getCreation(): ?\DateTimeInterface

    {

        return $this->creation;

    }



    public function setCreation(?\DateTimeInterface $creation): self

    {

        $this->creation = $creation;



        return $this;

    }



    public function getDateNaissance()

    {

        return $this->dateNaissance;

    }



    public function setDateNaissance($dateNaissance): self

    {

        $this->dateNaissance = $dateNaissance;



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



    public function getSiteWeb(): ?string

    {

        return $this->siteWeb;

    }



    public function setSiteWeb(?string $siteWeb): self

    {

        $this->siteWeb = $siteWeb;



        return $this;

    }



    public function getTelephone(): ?string

    {

        return $this->telephone;

    }



    public function setTelephone(?string $telephone): self

    {

        $this->telephone = $telephone;



        return $this;

    }



    public function getPhoto(): ?string

    {

        return $this->photo;

    }



    public function setPhoto(?string $photo): self

    {

        $this->photo = $photo;



        return $this;

    }



    /**

     * @return Collection|Communaute[]

     */

    public function getCommunautes(): Collection

    {

        return $this->communautes;

    }



    public function addCommunaute(Communaute $communaute): self

    {

        if (!$this->communautes->contains($communaute)) {

            $this->communautes[] = $communaute;

            $communaute->setUser($this);

        }



        return $this;

    }



    public function removeCommunaute(Communaute $communaute): self

    {

        if ($this->communautes->removeElement($communaute)) {

            // set the owning side to null (unless already changed)

            if ($communaute->getUser() === $this) {

                $communaute->setUser(null);

            }

        }



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

            $membre->setUser($this);

        }



        return $this;

    }



    public function removeMembre(Membres $membre): self

    {

        if ($this->membres->removeElement($membre)) {

            // set the owning side to null (unless already changed)

            if ($membre->getUser() === $this) {

                $membre->setUser(null);

            }

        }



        return $this;

    }



    /**

     * @return Collection|Post[]

     */

    public function getPosts(): Collection

    {

        return $this->posts;

    }



    public function addPost(Post $post): self

    {

        if (!$this->posts->contains($post)) {

            $this->posts[] = $post;

            $post->setUser($this);

        }



        return $this;

    }



    public function removePost(Post $post): self

    {

        if ($this->posts->removeElement($post)) {

            // set the owning side to null (unless already changed)

            if ($post->getUser() === $this) {

                $post->setUser(null);

            }

        }



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

            $commentaire->setUser($this);

        }



        return $this;

    }



    public function removeCommentaire(Commentaires $commentaire): self

    {

        if ($this->commentaires->removeElement($commentaire)) {

            // set the owning side to null (unless already changed)

            if ($commentaire->getUser() === $this) {

                $commentaire->setUser(null);

            }

        }



        return $this;

    }



    /**

     * @return Collection|PointUser[]

     */

    public function getPointUsers(): Collection

    {

        return $this->pointUsers;

    }



    public function addPointUser(PointUser $pointUser): self

    {

        if (!$this->pointUsers->contains($pointUser)) {

            $this->pointUsers[] = $pointUser;

            $pointUser->setUser($this);

        }



        return $this;

    }



    public function removePointUser(PointUser $pointUser): self

    {

        if ($this->pointUsers->removeElement($pointUser)) {

            // set the owning side to null (unless already changed)

            if ($pointUser->getUser() === $this) {

                $pointUser->setUser(null);

            }

        }



        return $this;

    }



    /**

     * @return Collection|Article[]

     */

    public function getArticles(): Collection

    {

        return $this->articles;

    }



    public function addArticle(Article $article): self

    {

        if (!$this->articles->contains($article)) {

            $this->articles[] = $article;

            $article->setUser($this);

        }



        return $this;

    }



    public function removeArticle(Article $article): self

    {

        if ($this->articles->removeElement($article)) {

            // set the owning side to null (unless already changed)

            if ($article->getUser() === $this) {

                $article->setUser(null);

            }

        }



        return $this;

    }



    /**

     * @return Collection|Chat[]

     */

    public function getChats(): Collection

    {

        return $this->chats;

    }



    public function addChat(Chat $chat): self

    {

        if (!$this->chats->contains($chat)) {

            $this->chats[] = $chat;

            $chat->setUser($this);

        }



        return $this;

    }



    public function removeChat(Chat $chat): self

    {

        if ($this->chats->removeElement($chat)) {

            // set the owning side to null (unless already changed)

            if ($chat->getUser() === $this) {

                $chat->setUser(null);

            }

        }



        return $this;

    }



    /**

     * @return Collection|Message[]

     */

    public function getMessages(): Collection

    {

        return $this->messages;

    }



    public function addMessage(Message $message): self

    {

        if (!$this->messages->contains($message)) {

            $this->messages[] = $message;

            $message->setUser($this);

        }



        return $this;

    }



    public function removeMessage(Message $message): self

    {

        if ($this->messages->removeElement($message)) {

            // set the owning side to null (unless already changed)

            if ($message->getUser() === $this) {

                $message->setUser(null);

            }

        }



        return $this;

    }



    /**

     * @return Collection|Like[]

     */

    public function getLikes(): Collection

    {

        return $this->likes;

    }



    public function addLike(Like $like): self

    {

        if (!$this->likes->contains($like)) {

            $this->likes[] = $like;

            $like->setUser($this);

        }



        return $this;

    }



    public function removeLike(Like $like): self

    {

        if ($this->likes->removeElement($like)) {

            // set the owning side to null (unless already changed)

            if ($like->getUser() === $this) {

                $like->setUser(null);

            }

        }



        return $this;

    }



    /**

     * @return Collection|Video[]

     */

    public function getVideos(): Collection

    {

        return $this->videos;

    }



    public function addVideo(Video $video): self

    {

        if (!$this->videos->contains($video)) {

            $this->videos[] = $video;

            $video->setUser($this);

        }



        return $this;

    }



    public function removeVideo(Video $video): self

    {

        if ($this->videos->removeElement($video)) {

            // set the owning side to null (unless already changed)

            if ($video->getUser() === $this) {

                $video->setUser(null);

            }

        }



        return $this;

    }



    /**

     * @return Collection|DownloadSpace[]

     */

    public function getFiles(): Collection

    {

        return $this->files;

    }



    public function addFile(DownloadSpace $file): self

    {

        if (!$this->files->contains($file)) {

            $this->files[] = $file;

            $file->setUser($this);

        }



        return $this;

    }



    public function removeFile(DownloadSpace $file): self

    {

        if ($this->files->removeElement($file)) {

            // set the owning side to null (unless already changed)

            if ($file->getUser() === $this) {

                $file->setUser(null);

            }

        }



        return $this;

    }



    /**

     * @return Collection|Forum[]

     */

    public function getForums(): Collection

    {

        return $this->forums;

    }



    public function addForum(Forum $forum): self

    {

        if (!$this->forums->contains($forum)) {

            $this->forums[] = $forum;

            $forum->setUser($this);

        }



        return $this;

    }



    public function removeForum(Forum $forum): self

    {

        if ($this->forums->removeElement($forum)) {

            // set the owning side to null (unless already changed)

            if ($forum->getUser() === $this) {

                $forum->setUser(null);

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

            $forumReponse->setUser($this);

        }



        return $this;

    }



    public function removeForumReponse(ForumReponse $forumReponse): self

    {

        if ($this->forumReponses->removeElement($forumReponse)) {

            // set the owning side to null (unless already changed)

            if ($forumReponse->getUser() === $this) {

                $forumReponse->setUser(null);

            }

        }



        return $this;

    }

    /**
     * @return Collection|Event[]
     */
    public function getEvents(): Collection
    {
        return $this->events;
    }

    public function addEvent(Event $event): self
    {
        if (!$this->events->contains($event)) {
            $this->events[] = $event;
            $event->setUser($this);
        }

        return $this;
    }

    public function removeEvent(Event $event): self
    {
        if ($this->events->removeElement($event)) {
            // set the owning side to null (unless already changed)
            if ($event->getUser() === $this) {
                $event->setUser(null);
            }
        }

        return $this;
    }

}

