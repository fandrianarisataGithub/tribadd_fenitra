<?php

namespace App\Entity;

use App\Repository\CommunauteRepository;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ORM\Entity(repositoryClass=CommunauteRepository::class)
 * @UniqueEntity("urlPublic", message="Cette url public existe déjà")
 */
class Communaute
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=100, nullable=true)
     */
    private $titre;
    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $sousTitre;
    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $descLong;
    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $descCourt;

    /**
     * @ORM\Column(type="string", length=100, nullable=true)
     */
    private $photoProfil;

    /**
     * @ORM\Column(type="string", length=100, nullable=true)
     */
    private $photoCouverture;

    /**
     * @ORM\Column(type="string", length=100, nullable=true)
     */
    private $siteWeb;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="communautes")
     */
    private $user;

    /**
     * @ORM\OneToMany(targetEntity=Membres::class, mappedBy="communaute")
     */
    private $membres;

    /**
     * @ORM\OneToMany(targetEntity=Post::class, mappedBy="communaute")
     */
    private $post;

    /**
     * @ORM\OneToMany(targetEntity=PointUser::class, mappedBy="communaute")
     */
    private $pointUsers;

    /**
     * @ORM\OneToMany(targetEntity=Article::class, mappedBy="communaute")
     */
    private $articles;

    /**
     * @ORM\OneToMany(targetEntity=Video::class, mappedBy="communaute")
     */
    private $videos;

    /**
     * @ORM\OneToMany(targetEntity=Roles::class, mappedBy="communaute", cascade="persist")
     */
    private $roles;



    /**

     * @ORM\OneToMany(targetEntity=DownloadSpace::class, mappedBy="communaue")

     */

    private $downloadSpaces;

    /**
     * @ORM\OneToMany(targetEntity=Chat::class, mappedBy="communaute")
     */
    private $chats;

    /**
     * @ORM\OneToMany(targetEntity=Categories::class, mappedBy="communaute", cascade="persist")
     */
    private $categories;

    /**
     * @ORM\Column(name="url_public", type="string", length=255, nullable=true)
     * @Assert\NotBlank(message="Ce champ est requis")
     */
    private $urlPublic;

    /**
     * @ORM\OneToMany(targetEntity=Forum::class, mappedBy="communaute")
     */
    private $forums;

    /**
     * @ORM\OneToMany(targetEntity=Newsletter::class, mappedBy="communaute")
     */
    private $newsletters;

    /**
     * @ORM\OneToMany(targetEntity=Event::class, mappedBy="communaute")
     */
    private $events;

    public function __construct(){
        $this->membres = new ArrayCollection();
        $this->posts = new ArrayCollection();
        $this->post = new ArrayCollection();
        $this->pointUsers = new ArrayCollection();
        $this->articles = new ArrayCollection();
        $this->videos = new ArrayCollection();
        $this->roles = new ArrayCollection();
        $this->downloadSpaces = new ArrayCollection();
        $this->chats = new ArrayCollection();
        $this->categories = new ArrayCollection();
        $this->forums = new ArrayCollection();
        $this->newsletters = new ArrayCollection();
        $this->events = new ArrayCollection();
    }

    public function getId(): ?int{
        return $this->id;
    }

    public function getTitre(): ?string{
        return $this->titre;

    }

    public function setTitre(?string $titre): self{
        $this->titre = $titre;
        return $this;

    }

    public function getSousTitre(): ?string{
        return $this->sousTitre;
    }

    public function setSousTitre(?string $sousTitre): self{
        $this->sousTitre = $sousTitre;
        return $this;
    }

    public function getDescLong(): ?string{
        return $this->descLong;
    }
    public function setDescLong(?string $descLong): self{
        $this->descLong = $descLong;
        return $this;
    }

    public function getDescCourt(): ?string{
        return $this->descCourt;
    }

    public function setDescCourt(?string $descCourt): self{
        $this->descCourt = $descCourt;

        return $this;
    }

    public function getPhotoProfil(): ?string{
        return $this->photoProfil;

    }

    public function setPhotoProfil(?string $photoProfil): self{
        $this->photoProfil = $photoProfil;

        return $this;

    }

    public function getPhotoCouverture(): ?string{
        return $this->photoCouverture;
    }

    public function setPhotoCouverture(?string $photoCouverture): self{
        $this->photoCouverture = $photoCouverture;

        return $this;
    }

    public function getSiteWeb(): ?string
    {
        return $this->siteWeb;
    }

    public function setSiteWeb(?string $siteWeb): self{
        $this->siteWeb = $siteWeb;

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

    /**
     * @return Collection|Membres[]
     */
    public function getMembres(): Collection{
        return $this->membres;
    }

    public function addMembre(Membres $membre): self
    {
        if (!$this->membres->contains($membre)) {
            $this->membres[] = $membre;
            $membre->setCommunaute($this);
        }
        return $this;
    }

    public function removeMembre(Membres $membre): self
    {
        if ($this->membres->removeElement($membre)) {
            // set the owning side to null (unless already changed)
            if ($membre->getCommunaute() === $this) {
                $membre->setCommunaute(null);
            }
        }
        return $this;
    }

    /**
     * @return Collection|Post[]
     */
    public function getPost(): Collection
    {
        return $this->post;
    }

    public function addPost(Post $post): self
    {
        if (!$this->post->contains($post)) {
            $this->post[] = $post;
            $post->setCommaunaute($this);
        }

        return $this;
    }

    public function removePost(Post $post): self
    {
        if ($this->post->removeElement($post)) {
            // set the owning side to null (unless already changed)
            if ($post->getCommaunaute() === $this) {
                $post->getCommaunaute(null);
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
            $pointUser->setCommunaute($this);
        }

        return $this;
    }

    public function removePointUser(PointUser $pointUser): self
    {
        if ($this->pointUsers->removeElement($pointUser)) {
            // set the owning side to null (unless already changed)
            if ($pointUser->getCommunaute() === $this) {
                $pointUser->setCommunaute(null);
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

    public function addArticle(Article $article): self{

        if (!$this->articles->contains($article)) {
            $this->articles[] = $article;
            $article->setCommunaute($this);
        }

        return $this;

    }

    public function removeArticle(Article $article): self{
        if ($this->articles->removeElement($article)) {
            // set the owning side to null (unless already changed)
            if ($article->getCommunaute() === $this) {
                $article->setCommunaute(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Video[]
     */
    public function getVideos(): Collection{
        return $this->videos;
    }

    public function addVideo(Video $video): self{
        if (!$this->videos->contains($video)) {
            $this->videos[] = $video;
            $video->setCommunaute($this);
        }
        return $this;
    }

    /**
     * @return Collection|Roles[]
     */
    public function getRoles(): Collection{
        return $this->roles;
    }

    public function addRole(Roles $role): self{
        if (!$this->roles->contains($role)) {
            $this->roles[] = $role;
            $role->setCommunaute($this);
        }

        return $this;
    }

    public function removeVideo(Video $video): self{
        if ($this->videos->removeElement($video)) {
            // set the owning side to null (unless already changed)
            if ($video->getCommunaute() === $this) {
                $video->setCommunaute(null);
            }
        }

        return $this;
    }

    public function removeRole(Roles $role): self{
        if ($this->roles->removeElement($role)) {
            // set the owning side to null (unless already changed)
            if ($role->getCommunaute() === $this) {
                $role->setCommunaute(null);
            }
        }
        return $this;
    }

    /**
     * @return Collection|DownloadSpace[]
     */
    public function getDownloadSpaces(): Collection{
        return $this->downloadSpaces;
    }

    public function addDownloadSpace(DownloadSpace $downloadSpace): self{
        if (!$this->downloadSpaces->contains($downloadSpace)) {
            $this->downloadSpaces[] = $downloadSpace;
            $downloadSpace->setCommunaue($this);
        }

        return $this;
    }

    public function removeDownloadSpace(DownloadSpace $downloadSpace): self{
        if ($this->downloadSpaces->removeElement($downloadSpace)) {
            // set the owning side to null (unless already changed)
            if ($downloadSpace->getCommunaue() === $this) {
                $downloadSpace->setCommunaue(null);
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
            $chat->setCommunaute($this);
        }
        return $this;

    }

    public function removeChat(Chat $chat): self
    {
        if ($this->chats->removeElement($chat)) {
            // set the owning side to null (unless already changed)
            if ($chat->getCommunaute() === $this) {
                $chat->setCommunaute(null);
            }
        }

        return $this;
    }
    /**
     * @return Collection|Categories[]
     */
    public function getCategories(): Collection
    {
        return $this->categories;
    }

    public function addCategory(Categories $category): self
    {
        if (!$this->categories->contains($category)) {
            $this->categories[] = $category;
            $category->setCommunaute($this);
        }

        return $this;
    }

    public function removeCategory(Categories $category): self
    {
        if ($this->categories->removeElement($category)) {
            // set the owning side to null (unless already changed)
            if ($category->getCommunaute() === $this) {
                $category->setCommunaute(null);
            }
        }

        return $this;
    }

    public function getUrlPublic(): ?string
    {
        return $this->urlPublic;
    }

    public function setUrlPublic(?string $urlPublic): self
    {
        $this->urlPublic = $urlPublic;

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
            $forum->setCommunaute($this);
        }
        return $this;
    }

    public function removeForum(Forum $forum): self
    {
        if ($this->forums->removeElement($forum)) {
            // set the owning side to null (unless already changed)
            if ($forum->getCommunaute() === $this) {
                $forum->setCommunaute(null);
            }
        }
        return $this;
    }

    public function isAbonneNewsLetter(User $user):bool {
        foreach ($this->membres as $membre){
            if($membre->getAbonneNewsLetter() != null and $membre->getAbonneNewsLetter() == true and $membre->getUser() == $user){
                return true;
            }
        }
        return false;
    }

    /**
     * @return Collection|Newsletter[]
     */
    public function getNewsletters(): Collection
    {
        return $this->newsletters;
    }

    public function addNewsletter(Newsletter $newsletter): self
    {
        if (!$this->newsletters->contains($newsletter)) {
            $this->newsletters[] = $newsletter;
            $newsletter->setCommunaute($this);
        }

        return $this;
    }

    public function removeNewsletter(Newsletter $newsletter): self
    {
        if ($this->newsletters->removeElement($newsletter)) {
            // set the owning side to null (unless already changed)
            if ($newsletter->getCommunaute() === $this) {
                $newsletter->setCommunaute(null);
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
            $event->setCommunaute($this);
        }

        return $this;
    }

    public function removeEvent(Event $event): self
    {
        if ($this->events->removeElement($event)) {
            // set the owning side to null (unless already changed)
            if ($event->getCommunaute() === $this) {
                $event->setCommunaute(null);
            }
        }

        return $this;
    }
}

