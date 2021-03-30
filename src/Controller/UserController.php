<?php

namespace App\Controller;

use ZipArchive;
use App\Entity\Chat;
use App\Entity\Like;
use App\Entity\Post;
use App\Entity\User;
use App\Entity\Emoji;
use App\Entity\Forum;
use App\Entity\Roles;
use App\Entity\Video;
use App\Entity\Article;
use App\Entity\Membres;
use App\Entity\Message;
use App\Form\RolesType;
use App\Entity\PointUser;
use App\Entity\Categories;
use App\Entity\Communaute;
use App\Entity\Newsletter;
use App\Entity\Commentaires;
use App\Entity\ForumReponse;
use App\Form\CommunauteType;
use App\Form\NewsLetterType;
use App\Entity\DownloadSpace;
use App\Entity\DroitsUtilisateur;
use App\Repository\ChatRepository;
use App\Repository\LikeRepository;
use App\Repository\PostRepository;
use App\Repository\UserRepository;
use App\Repository\ForumRepository;
use App\Repository\RolesRepository;
use App\Repository\VideoRepository;
use Symfony\Component\Asset\Package;
use Symfony\Component\Finder\Finder;
use App\Repository\ArticleRepository;
use App\Repository\MembresRepository;
use App\Repository\NewLetterRepository;
use App\Repository\PointUserRepository;
use App\Repository\CategoriesRepository;
use App\Repository\CommunauteRepository;
use App\Repository\NewsletterRepository;
use Doctrine\ORM\EntityManagerInterface;
use phpDocumentor\Reflection\Types\This;
use Symfony\Component\Asset\PathPackage;
use App\Repository\ForumReponseRepository;
use Doctrine\ORM\NonUniqueResultException;
use App\Repository\DownloadSpaceRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Routing\Exception\RouteNotFoundException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Asset\VersionStrategy\EmptyVersionStrategy;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Tests\Encoder\PasswordEncoder;
use Symfony\Component\Security\Core\Encoder\PasswordEncoderInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

/**
 * Class UserController
 * @package App\Controller
 * @Route("/in")
 */
class UserController extends AbstractController
{
    private $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
        /*if($this->getUser() == null){
            return $this->redirectToRoute('registerUser');
        }*/
    }

    /**
     * @Route("/new/community.html", name="newCommunity")
     * @param Request $request
     * @return Response
     * @throws \Exception
     */
    public function newCommunity(Request $request)
    {
        $communaute = new Communaute();
        $post = new Post();
        $form = $this->createFormBuilder($communaute)
            ->add('titre', TextType::class, ['attr' => ['placeholder' => 'Titre']])
            ->add('sousTitre', TextType::class, ['attr' => ['placeholder' => 'Sous-titre']])
            ->add('descCourt', TextareaType::class, ['attr' => ['placeholder' => 'Description courte'], 'required' => false,])
            ->add('siteWeb', TextType::class, ['attr' => ['placeholder' => 'Site web'], 'required' => false,])->getForm();
        $form->handleRequest($request);
        if ($request->isXmlHttpRequest()) {
            // $infos = $form->get('form');
            $user = $this->getUser();

            $resp = [];
            $resp['resp'] = 'error';
            $resp['id'] = 0;
            if (isset($_FILES['photoProfil']) AND $_FILES['photoProfil']['error'] == 0 && isset($_FILES['photoCouverture']) AND $_FILES['photoCouverture']['error'] == 0) {
                $photoProfil = pathinfo($_FILES['photoProfil']['name']);
                $photoCouverture = pathinfo($_FILES['photoCouverture']['name']);
                $extensionProfil = $photoProfil['extension'];
                $extensionCouverture = $photoCouverture['extension'];
                $extensions_autorisees = array('jpg', 'jpeg', 'gif', 'png', 'PNG', 'JPG', 'JPEG', 'GIF');
                if (in_array($extensionProfil, $extensions_autorisees) and in_array($extensionCouverture, $extensions_autorisees)) {
                    $nameFileProfil = $photoProfil['filename'];
                    $nameFileCouverture = $photoCouverture['filename'];
                    while (file_exists('profil/' . $nameFileProfil . '.' .$extensionProfil)) {
                        $nameFileProfil = $nameFileProfil . '-copy';
                    }
                    while (file_exists('couverture/' . $nameFileCouverture . '.' . $extensionCouverture)) {
                        $nameFileCouverture = $nameFileCouverture . '-copy';
                    }
                    move_uploaded_file($_FILES['photoProfil']['tmp_name'], 'profil/' . $nameFileProfil . '.' .$extensionProfil);
                    move_uploaded_file($_FILES['photoCouverture']['tmp_name'], 'couverture/' . $nameFileCouverture . '.' . $extensionCouverture);
                    $communaute->setPhotoProfil($nameFileProfil.'.'.$extensionProfil)
                        ->setPhotoCouverture($nameFileCouverture . '.' . $extensionCouverture);
                    $post->setImageEncadre($nameFileProfil.'.'.$extensionProfil);
                }

                if (isset($_POST['sousTitre'], $_POST['titre'], $_POST['descCourt'])) {
                    $communaute->setTitre($_POST['titre'])
                        ->setUser($this->getUser())
                        ->setSousTitre($_POST['sousTitre'])
                        ->setDescLong($_POST['decriptionLongue'])
                        ->setDescCourt(nl2br($_POST['descCourt']))->setUrlPublic($_POST['urlPublic']);
                    $roleAdmin = new Roles();
                    $droitAdmin = new DroitsUtilisateur();
                    $droitAdmin->setCreation(true)
                        ->setModification(true)
                        ->setVisualisation(true)
                        ->setSuppression(true)
                        ->setCommentaire(true);
                    $roleAdmin->setDroitsUtilisateur($droitAdmin);
                    $roleAdmin->setLibelle('administrateur');
                    $this->em->persist($roleAdmin);
                    $communaute->addRole($roleAdmin);
                    $roleAuthor = new Roles();
                    $droitAuthor = new DroitsUtilisateur();
                    $droitAuthor->setCreation(true)
                        ->setModification(true)
                        ->setVisualisation(true)
                        ->setSuppression(false)
                        ->setCommentaire(true);
                    $roleAuthor->setDroitsUtilisateur($droitAuthor);
                    $roleAuthor->setLibelle('auteur');
                    $this->em->persist($roleAuthor);
                    $communaute->addRole($roleAuthor);
                    $roleMember = new Roles();
                    $droitMember = new DroitsUtilisateur();
                    $droitMember->setCreation(false)
                        ->setModification(true)
                        ->setVisualisation(true)
                        ->setSuppression(false)
                        ->setCommentaire(true);
                    $roleMember->setDroitsUtilisateur($droitMember);
                    $roleMember->setLibelle('membre');
                    $this->em->persist($roleMember);
                    $communaute->addRole($roleMember);
                    $category = new Categories();
                    $category->setNom('général')
                        ->setCreatedAt(new \DateTime('now'));
                    $communaute->addCategory($category);
                    $this->em->persist($communaute);
                    $this->em->flush();
                    $post->setShortDescription($_POST['descCourt'])
                        ->setTitre('Nouvelle communauté : '.$_POST['titre'])
                        ->setType('C')
                        ->setUser($user)
                        ->setPublishedAt(new \DateTime())
                        ->setCommaunaute($communaute)
                        ->setArticleId($communaute->getId());
                    $this->em->persist($post);
                    $this->em->flush();

                    $this->addFlash('success', 'Votre tribu a été fondée');
                    $resp['resp'] = 'success';
                    $resp['data'] = $communaute->getUrlPublic();
                }
            }
            return new JsonResponse($resp);
        }
        return $this->render('user/newCommunity.html.twig', ['form' => $form->createView()]);
    }

    /**
     * @param Communaute $communaute
     * @param CommunauteRepository $communauteRepository
     * @Route("/verif/url")
     * @return JsonResponse
     */
    public function verifUrlPublic(CommunauteRepository $communauteRepository){
        $comm = $communauteRepository->findByUrlPublic($_GET['url']);
        return new JsonResponse(["taille"=>sizeof($comm)]);
    }

    /**
     * @Route("/{urlPublic}/presentation", name="UsershowPresentation")
     * @param Communaute $communaute
     * @param Request $request
     * @param MembresRepository $repositoryMembre
     * @return Response
     */
    public function showPresentation(Communaute $communaute, Request $request, MembresRepository $repositoryMembre){
        $user = $this->getUser();
        if($user == null){
            return $this->redirect('/'.$communaute->getUrlPublic().'/presentation');
        }
        $membre = $repositoryMembre->findOneBy(['user' => $user, 'communaute' => $communaute]);
        return $this->render('presentation.html.twig', [
            'communaute' => $communaute,
            'membre' => $membre,
            'user' => $user,
        ]);
    }

    /**
     * @Route("/{urlPublic}/gestion-categories", name="gestionCategorie")
     * @param Communaute $communaute
     * @param MembresRepository $membresRepository
     * @param CategoriesRepository $categoriesRepository
     * @return RedirectResponse|Response
     */
    public function gestionCategorie(Communaute $communaute, MembresRepository $membresRepository, CategoriesRepository $categoriesRepository)
    {
        $user = $this->getUser();
        $membre = $membresRepository->findOneBy(['user' => $user, 'communaute' => $communaute]);
        $categories = $categoriesRepository->findBy(['communaute' => $communaute], ['createdAt' => 'DESC']);
        return $this->render('user/gestionCategories.html.twig', [
            'communaute' => $communaute,
            'membre' => $membre,
            'categories' => $categories
        ]);
    }

    /**
     * @Route("/ajouter-categorie-{id}.html", name="addCategory")
     * @param Request $request
     * @param Communaute $communaute
     * @param CategoriesRepository $categoriesRepository
     * @return JsonResponse
     * @throws \Exception
     */
    public function addCategory(Request $request, Communaute $communaute, CategoriesRepository $categoriesRepository) {
        if ($request->isXmlHttpRequest()){
            $result = "error";
            $id = null;
            $count = 0;
            if (trim($request->request->get('category')) != '') {
                if ($request->request->get('edit') && $request->request->get('id') != null) {
                    $category = $categoriesRepository->find($request->request->get('id'));
                    if ($category == null)
                        return new JsonResponse(['result' => $result, 'data' => ['id' => $id, 'articles' => $count]]);
                    $count = $category->getArticles()->count();
                    if (strtolower($category->getNom()) == 'général')
                        return new JsonResponse(['result' => 'denied']);
                    if ($category->getNom() == $request->request->get('category'))
                        return new JsonResponse(['result' => 'success', 'data' => ["id" => $category->getId(), 'articles' => $count]]);
                    else if (!$categoriesRepository->findWithoutCurent($communaute, $request->request->get('category'), $category->getId()))
                        return new JsonResponse(['result' => 'exist', 'data' => ['id' => $category->getId(), 'articles' => $count]]);
                } else {
                    if ($categoriesRepository->findOneBy(['communaute' => $communaute, 'nom' => $request->request->get('category')]))
                        return new JsonResponse(['result' => 'exist', 'data' => ['id' => $id, 'articles' => $count]]);
                    $category = new Categories();
                    $category->setCommunaute($communaute)
                        ->setCreatedAt(new \DateTime('now'));
                }
                $category->setNom($request->request->get('category'));
                $this->em->persist($category);
                $this->em->flush();
                $count = $category->getArticles()->count();
                $result = 'success';
                $id = $category->getId();
            }
            return new JsonResponse(['result' => $result, 'data' => ["id" => $id, "articles" => $count]]);
        }
        throw new NotFoundHttpException('La page n\'existe pas');
    }

    /**
     * @Route("/remove-category-{id}.html", name="removeCategory")
     * @param Request $request
     * @param Categories $categories
     * @return JsonResponse
     */
    public function removeCategory(Request $request, Categories $categories)
    {
        if ($request->isXmlHttpRequest()) {
            $result = 'error';
            if (strtolower($categories->getNom()) == 'général')
                return new JsonResponse(['result' => 'denied']);
            if ($categories == null)
                return new JsonResponse(['result' => $result]);
            else {
                if ($categories->getArticles()->count() > 0)
                    return new JsonResponse(['result' => 'denied']);
                $this->em->remove($categories);
                $this->em->flush();
                $result = 'success';
                return new JsonResponse(['result' => $result]);
            }

        }
        throw new NotFoundHttpException('La page n\'existe pas');
    }

    /**
     * @param Request $request
     * @param CommunauteRepository $repository
     * @return Response
     * @Route("/myCommunaute.html", name="myCommunaute")
     */
    public function myCommunaute(Request $request, CommunauteRepository $repository)
    {
        $user = $this->getUser();
        if ($user == null) {
            return $this->redirectToRoute('login');
        } else {
            $user = $this->getUser();
            $communaute = $repository->findAll();
            $communities = [];
            foreach ($communaute as $comm) {
                foreach ($comm->getMembres() as $membre)
                {
                    if($membre->getUser() == $user){
                        $communities[] = $comm;
                    }else{
                        $communities[] = $comm;
                    }

                }
            }
            return $this->render('user/myCommunaute.html.twig', ['myCommunities' => $communities]);
        }
    }

    /**
     * @Route("/other/Communaute.html", name="OthersCommunaute")
     * @param CommunauteRepository $repository
     * @return Response
     */
    public function OthersCommunity(CommunauteRepository $repository) {
        $user = $this->getUser();
        if ($user == null) {
            return $this->redirectToRoute('login');
        } else {
            $communaute = $repository->findAll();
            $myCommunity = [];
            foreach ($communaute as $comm) {
                foreach ($comm->getMembres() as $membre)
                    if ($membre->getUser() == $user)
                        $myCommunity[] = $comm;
                if ($comm->getUser() == $user)
                    $myCommunity[] = $comm;
            }
            return $this->render('user/othersCommunity.html.twig', [
                'myCommunities' => $myCommunity,
                'communaute' => $communaute
            ]);
        }
    }

    /**
     * @param Communaute $communaute
     * @param Request $request
     * @param MembresRepository $repository
     * @param PostRepository $postRepository
     * @param ArticleRepository $articleRepository
     * @param RolesRepository $rolesRepository
     * @param VideoRepository $videoRepository
     * @return Response
     * @throws \Exception
     * @Route("/{urlPublic}", name="ShowmyCommunaute")
     */
    public function ShowmyCommunaute(Communaute $communaute, Request $request, MembresRepository $repository, PostRepository $postRepository, ArticleRepository $articleRepository, RolesRepository $rolesRepository, VideoRepository $videoRepository)
    {
        if($this->getUser() == null){
            return $this->redirect('/'.$communaute->getUrlPublic());
        }
            $membre = new Membres();
            $user = $this->getUser();
            $member = $repository->findOneBy(['user' => $user, 'communaute' => $communaute]);
            $arr = [];
            foreach($communaute->getPost() as $p){
                $arr[] = $p;
            }
            $post = new Post();
            $form = $this->createFormBuilder($post)
                ->add('shortDescription', TextareaType::class, ['required' => true, 'attr' => [
                    'placeholder' => 'Description'
                ]])
                ->add('titre', TextType::class, ['required' => true, 'attr' => [
                    'placeholder' => 'Titre'
                ]])
                ->add('contenus', TextareaType::class, ['required' => true])
                ->getForm();
            if (isset($_GET['linkRejoindre']) && $_GET['linkRejoindre'] == $user->getId()) {
                $role = $rolesRepository->findOneBy(['communaute' => $communaute, 'libelle' => 'membre']);
                $membre->setCommunaute($communaute)
                    ->setUser($user)->setAbonneNewsLetter(true)
                    ->setRole($role)
                    ->setDateDebut(new \DateTime());
                $this->em->persist($membre);
                $this->em->flush();
                if ($request->query->get('route') != null)
                   return new RedirectResponse($request->query->get('route')."?id=".$request->query->get('params'));
               
                else
                    return $this->redirectToRoute('ShowmyCommunaute', ['urlPublic' => $communaute->getUrlPublic()]);
            }
            if (isset($_GET['linkQuit']) && $_GET['linkQuit'] == $user->getId()) {
                $m = $this->em->getRepository(Membres::class)->findOneBy(['user' => $user, 'communaute' => $communaute]);
                if ($m) {
                    $this->em->remove($m);
                    $this->em->flush();
                }
                return $this->redirectToRoute('ShowmyCommunaute', ['urlPublic' => $communaute->getUrlPublic()]);
            }
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                if (isset($_FILES['image_encadre']) AND $_FILES['image_encadre']['error'] == 0) {
                    $encadreInfo = pathinfo($_FILES['image_encadre']['name']);
                    $extensionencadre = $encadreInfo['extension'];
                    $extensions_autorisees = array('jpg', 'jpeg', 'gif', 'png', 'PNG', 'JPG', 'JPEG', 'GIF');

                    if(in_array($extensionencadre,$extensions_autorisees)) {
                        $nameFileEncdre = $encadreInfo['basename'];

                        $fileExiste[]=[];
                        $finder = new Finder();
                        $finder->files()->in('../public/imgEncadre/');
                        foreach ($finder as $file) {
                            // $absoluteFilePath = $file->getRealPath();
                            $fileNameWithExtension = $file->getRelativePathname();
                            $fileExiste[] = $fileNameWithExtension;

                            // ...
                        }
                        if(in_array($nameFileEncdre,$fileExiste)){
                            $nomFichier = $encadreInfo['filename'].'-copy'.'.'.$encadreInfo['extension'];
                        }else{
                            $nomFichier = $nameFileEncdre;
                        }
                        move_uploaded_file($_FILES['image_encadre']['tmp_name'], 'imgEncadre/' . $nomFichier);
                        $post->setImageEncadre($nomFichier)
                            ->setPublishedAt(new \DateTime())
                            ->setCommaunaute($communaute)
                            ->setType('P')
                            ->setUser($user);
                        $this->em->persist($post);
                        $this->em->flush();

                        return $this->redirectToRoute('ShowmyCommunaute', ['urlPublic' => $communaute->getUrlPublic()]);
                    }
                }
            }
            if ($request->isXmlHttpRequest()) {
                $data = [];
                $data['resp'] = 'error';
                if (isset($_POST['commentaire'])) {
                    $commentaire = new Commentaires();
                    $commentaire->setCommentedAt(new \DateTime())
                        ->setUser($user)
                        ->setContenus(nl2br($_POST['commentaire']));
                    if (isset($_POST['idPost'])) {
                        $commentaire->setPost($postRepository->find($_POST['idPost']));
                    } else if (isset($_POST['idArticle'])) {
                        $commentaire->setArticle($articleRepository->find($_POST['idArticle']));
                    } else if (isset($_POST['idVideo'])) {
                        $commentaire->setVideo($videoRepository->find($_POST['idVideo']));
                    }
                    $this->em->persist($commentaire);
                    $this->em->flush();
                    $data['data'] = [
                        'id' => $commentaire->getId(),
                        'commentedAt' => $commentaire->getCommentedAt(),
                        'contenus' => $commentaire->getContenus(),
                        'user' => [
                            'username' => $commentaire->getUser()->getUsername(),
                            'photo' => $commentaire->getUser()->getPhoto()
                        ],
                    ];
                    if (isset($_POST['idPost'])) {
                        $data['data'] += [
                            'posts' => $commentaire->getPost()->getId(),
                            'count' => count($postRepository->find($_POST['idPost'])->getCommentaires())
                        ];
                    }
                    $data['resp'] = 'success';
                }
                return new JsonResponse($data);
            }
            return $this->render('user/showCommunaute.html.twig', [
                'communaute' => $communaute,
                'membre' => $member,
                'form' => $form->createView(),
                'post'=>$arr
            ]);
    }

    /**
     * @param Membres $membres
     * @Route("/subscribe/{id}", name="subscribeCommunauty")
     * @return JsonResponse
     */
    public function subscribeNewsLetter(Membres $membres){
        if($membres->getAbonneNewsLetter() == null or $membres->getAbonneNewsLetter() == false){
            $membres->setAbonneNewsLetter(true);
        }else{
            $membres->setAbonneNewsLetter(false);
        }
        $this->em->persist($membres);
        $this->em->flush();
        return $this->json(['id'=>$membres->getId(), 'subscribe'=>$membres->getAbonneNewsLetter()]);
    }
    /**
     * @param Post $post
     * @param Request $request
     * @param MembresRepository $repository
     * @return RedirectResponse|Response
     * @throws \Exception
     * @Route("/{urlPublic}/edit/post", name="editPost")
     */
    public function editPost(Communaute $communaute, Request $request, MembresRepository $repository, PostRepository $postRepository){
        $post= $postRepository->find($_GET['id']);
        $user = $this->getUser();
        $membre = $repository->findOneBy(['user' => $user, 'communaute' => $post->getCommaunaute()]);
        if ($post->getCommaunaute()->getUser() != $user) {
            if ($membre == null || $membre->getRole()->getDroitsUtilisateur()->getModification() != 1)
            return $this->redirectToRoute($this->redirectToRoute('ShowmyCommunaute', ['urlPublic' => $post->getCommaunaute()->getUrlPublic()]));
        }
        $form = $this->createFormBuilder($post)
            ->add('shortDescription', TextareaType::class, ['required' => true, 'attr' => [
                'placeholder' => 'Description'
            ]])
            ->add('titre', TextType::class, ['required' => true, 'attr' => [
                'placeholder' => 'Meta titre'
            ]])
            ->add('contenus', TextareaType::class, ['required' => true])
            ->getForm();
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $info = $request->get('form');
            if (isset($_FILES['image_encadre']) AND $_FILES['image_encadre']['error'] == 0) {

                $encadreInfo = pathinfo($_FILES['image_encadre']['name']);
                $extensionencadre = $encadreInfo['extension'];
                $extensions_autorisees = array('jpg', 'jpeg', 'gif', 'png', 'PNG', 'JPG', 'JPEG', 'GIF');

                if(in_array($extensionencadre,$extensions_autorisees)) {
                    $nameFileEncdre = $encadreInfo['filename'];
                    if ($post->getImageEncadre() != null)
                        unlink('imgEncadre/'.$post->getImageEncadre());
                    while (file_exists('imgEncadre/' . $nameFileEncdre . '.' .$extensionencadre)) {
                        $nameFileEncdre = $nameFileEncdre . '-copy';
                    }
                    move_uploaded_file($_FILES['image_encadre']['tmp_name'], 'imgEncadre/' . $nameFileEncdre . '.' .$extensionencadre);
                    $post->setImageEncadre($nameFileEncdre . '.' .$extensionencadre);
                }
            }
            $post->setPublishedAt(new \DateTime())
                ->setUser($user)
                ->setShortDescription($info['shortDescription'])
                ->setTitre($info['titre'])
                ->setContenus($info['contenus']);
            $this->em->persist($post);
            $this->em->flush();
            $this->addFlash('success', 'Votre publication est bien modifiée!');
            return $this->redirectToRoute('editPost', ['urlPublic' => $post->getCommaunaute()->getUrlPublic(),'id'=>$post->getId()]);

        }
        return $this->render('user/editPost.html.twig',[
            'membre' => $membre,
            'form' => $form->createView(),
            'post' => $post,
            'communaute' => $post->getCommaunaute()
        ]);
    }

    /**
     * @param CommunauteRepository $repository
     * @return RedirectResponse|Response
     * @Route("/accueil/index", name="user")
     */
    public function index(CommunauteRepository $repository)
    {
        if ($this->getUser() == null) {
            return $this->redirectToRoute('login');
        } else {
            $user = $this->getUser();
            $communaute = $repository->findAll();
            $myCommunities = [];
            foreach ($communaute as $comm) {
                foreach ($comm->getMembres() as $membre)
                    if ($membre->getUser() == $user && $membre->getCommunaute() === $comm)
                        $myCommunities[] = $comm;
                if ($comm->getUser() == $user){
                    if (!in_array($comm,$myCommunities)) {
                       $myCommunities[] = $comm;
                    }
                }
            }
            return $this->render('user/index.html.twig', [
                'communaute' => $communaute,
                'myCommunities' => $myCommunities
            ]);
        }
    }

    /**
     * @Route("/{urlPublic}/edit", name="editMyCommunaute")
     * @param Request $request
     * @param Communaute $communaute
     * @return Response
     * @throws \Exception
     */
    public function editMyCommunaute(Request $request, Communaute $communaute)
    {
        $post = new Post();
        $user = $this->getUser();
        if($user == null){
            return $this->redirect('/'.$communaute->getUrlPublic());
        }
        $form = $this->createFormBuilder($communaute)
            ->add('titre', TextType::class, ['attr' => ['placeholder' => 'Titre']])
            ->add('sousTitre', TextType::class, ['attr' => ['placeholder' => 'Sous-titre']])
            ->add('descCourt', TextareaType::class, ['attr' => ['placeholder' => 'Description courte'], 'required' => false,])
            ->add('siteWeb', TextType::class, ['attr' => ['placeholder' => 'Site web'], 'required' => false,])->getForm();
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            // dd($_POST['urlPublic']);
            $info = $request->get('form');
            if ($request->files->get('photoProfil') || $request->files->get('photoCouverture')) {
                $extensions_autorisees = array('jpg', 'jpeg', 'gif', 'png', 'PNG', 'JPG', 'JPEG', 'GIF');
                if($_FILES['photoProfil'] AND $_FILES['photoProfil']['error'] == 0){
                    $photoProfil = pathinfo($_FILES['photoProfil']['name']);
                    $extensionProfil = $photoProfil['extension'];
                    if(in_array($extensionProfil, $extensions_autorisees)){
                        $nameFileProfil = $photoProfil['filename'];
                        while (file_exists('couverture/' . $nameFileProfil . '.' .$extensionProfil)) {
                            $nameFileProfil = $nameFileProfil . '-copy';
                        }
                        move_uploaded_file($_FILES['photoProfil']['tmp_name'], 'couverture/' . $nameFileProfil . '.' .$extensionProfil);
                        $communaute->setPhotoCouverture($nameFileProfil . '.' .$extensionProfil);
                    }
                }
                if($_FILES['photoCouverture'] AND $_FILES['photoCouverture']['error'] == 0){
                    $photoCouverture = pathinfo($_FILES['photoCouverture']['name']);
                    $extensionCouverture = $photoCouverture['extension'];
                    if(in_array($extensionCouverture, $extensions_autorisees)){
                        $nameFileCouverture = $photoCouverture['basename'];
                        $communaute->setPhotoProfil($nameFileCouverture);
                        $post->setImageEncadre($nameFileCouverture);
                        $nameFileCouverture = $photoCouverture['filename'];
                        while (file_exists('profil/' . $nameFileCouverture . '.' .$extensionCouverture)){
                            $nameFileCouverture = $nameFileCouverture . '-copy';
                        }
                        // dd("sd");
                        move_uploaded_file($_FILES['photoCouverture']['tmp_name'], 'profil/' . $nameFileCouverture . '.' .$extensionCouverture);
                        $communaute->setPhotoProfil($nameFileCouverture . '.' . $extensionCouverture);
                        $post->setImageEncadre($nameFileCouverture . '.' . $extensionCouverture);
                    }
                }
                $communaute->setTitre($info['titre'])
                    ->setUser($this->getUser())->setUrlPublic($_POST['urlPublic'])
                    ->setSousTitre($info['sousTitre'])
                    ->setDescLong($request->get('decriptionLongue'))
                    ->setDescCourt(nl2br($info['descCourt']));
                $this->em->persist($communaute);
                $this->em->flush();
                $post->setUser($user)
                    ->setCommaunaute($communaute)
                    ->setPublishedAt(new \DateTime())
                    ->setTitre('Modification de la communauté : '.$info['titre'])
                    ->setShortDescription($info['descCourt'])
                    ->setType('MC')
                    ->setArticleId($communaute->getId());
                $this->em->persist($post);
                $this->em->flush();
                return $this->redirectToRoute('ShowmyCommunaute',['urlPublic'=>$communaute->getUrlPublic()]);

            } else {
                $communaute->setTitre($info['titre'])
                    ->setUser($this->getUser())->setUrlPublic($_POST['urlPublic'])
                    ->setSousTitre($info['sousTitre'])
                    ->setDescLong($request->get('decriptionLongue'))
                    ->setDescCourt(nl2br($info['descCourt']));
                $this->em->persist($communaute);
                $this->em->flush();
                $post->setUser($user)
                    ->setCommaunaute($communaute)
                    ->setPublishedAt(new \DateTime())
                    ->setTitre('Modification de la communauté : '.$info['titre'])
                    ->setShortDescription($info['descCourt'])
                    ->setType('MC')
                    ->setArticleId($communaute->getId())->setImageEncadre($communaute->getPhotoProfil());
                $this->em->persist($post);
                $this->em->flush();
                return $this->redirectToRoute('ShowmyCommunaute',['urlPublic'=>$communaute->getUrlPublic()]);
            }
        }
        return $this->render('user/editMyCommunaute.html.twig', ['form' => $form->createView(), 'com'=>$communaute]);
    }

    /**
     * @Route("/{urlPublic}/post", name="showPostCommunaute")
     * @param Communaute $communaute
     * @param Request $request
     * @param MembresRepository $repository
     * @param PostRepository $postRepository
     * @return Response
     */
    public function showPostCommunaute(Communaute $communaute, Request $request, MembresRepository $repository, PostRepository $postRepository){
        $post=$postRepository->find($_GET['id']);
        $user = $this->getUser();
        if($user == null){
            return $this->redirect('/'.$communaute->getUrlPublic().'/post?id='.$_GET['id']);
        }
        $membre = $repository->findOneBy(['user' => $user, 'communaute' => $post->getCommaunaute()]);
        return $this->render('user/showPostCommunaute.html.twig', [
            'post' => $post,
            'communaute' => $post->getCommaunaute(),
            'membre' => $membre
        ]);
    }

    /**
     * @Route("/{urlPublic}/articles", name="userShowArticles")
     * @param Communaute $communaute
     * @param Request $request
     * @param MembresRepository $membresRepository
     * @return Response
     */
    public function getArticles(Communaute $communaute, Request $request, MembresRepository $membresRepository) {
        $user = $this->getUser();
        if($user == null){
            return $this->redirect('/'.$communaute->getUrlPublic().'/articles');
        }
        $membre = $membresRepository->findOneBy(['user' => $user, 'communaute' => $communaute]);
        return $this->render('user/showArticleCommunaute.html.twig', [
            'communaute' => $communaute,
            'membre' => $membre
        ]);
    }

    /**
     * @Route("/{urlPublic}/nouvel-article", name="newArticle")
     * @param Communaute $communaute
     * @param Request $request
     * @param MembresRepository $membresRepository
     * @return Response
     * @throws \Exception
     */
    public function addNewArticle(Communaute $communaute, Request $request, MembresRepository $membresRepository) {
        $article = new Article();
        $post = new Post();
        $user = $this->getUser();
        $membre = $membresRepository->findOneBy(['user' => $user, 'communaute' => $communaute]);
        if ($communaute->getUser() != $user) {
            if ($membre == null || $membre->getRole()->getDroitsUtilisateur()->getCreation() != 1)
                return $this->redirectToRoute($this->redirectToRoute('showArticles', ['urlPublic' => $communaute->getUrlPublic()]));
        }
        $form = $this->createFormBuilder($article)
            ->add('titre', TextType::class, [
                "required" => true,
                'attr' => ["placeholder" => "titre"]
            ])
            ->add('contenu', TextareaType::class, [
                "required" => true,
            ])
            ->add('introduction', TextareaType::class, [
                "required" => true,
                "attr" => ["placeholder" => "introduction"]
            ])
            ->add('categories', EntityType::class, [
                'class' => Categories::class,
                'query_builder' => function (CategoriesRepository $cr) use ($communaute) {
                    return $cr->createQueryBuilder('c')
                        ->where('c.communaute = :comm')
                        ->setParameter('comm', $communaute);
                },
                'choice_label' => 'nom',
                'expanded' => false
            ])
            ->getForm();

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $infos = $request->get('form');
            if (isset($_FILES)) {
                $extensions_autorisees = array('jpg', 'jpeg', 'gif', 'png', 'PNG', 'JPG', 'JPEG', 'GIF');
                if ($_FILES['image'] and $_FILES['image']['error'] == 0) {
                    $image = pathinfo($_FILES['image']['name']);
                    $extension = $image['extension'];
                    if (in_array($extension, $extensions_autorisees)) {
                        $nameFile = $image['filename'];
                        while (file_exists('articles/' . $nameFile . '.' . $extension)) {
                            $nameFile = $nameFile . '-copy';
                        }
                        move_uploaded_file($_FILES['image']['tmp_name'], 'articles/' . $nameFile . '.' . $extension);
                        $article->setImage($nameFile . '.' . $extension);
                        $post->setImageEncadre($nameFile . '.' . $extension);
                    }
                }
                $post->setTitre('Ajout d\'un nouvel article : '.$infos['titre'])
                    ->setCommaunaute($communaute)
                    ->setContenus('')
                    ->setPublishedAt(new \DateTime())
                    ->setShortDescription($infos['introduction'])
                    ->setType('A')
                    ->setUser($user);

                $article->setUser($user);
                $article->setCommunaute($communaute);
                $article->setCreatedAt(new \DateTime());
                $this->em->persist($article);

                $this->em->flush();
                $post->setArticleId($article->getId());
                $this->em->persist($post);
                $this->em->flush();
                return $this->redirectToRoute('userShowArticles', ['urlPublic' => $communaute->getUrlPublic()]);
            }
        }
        return $this->render('user/addNewArticle.html.twig', [
            'communaute' => $communaute,
            'form' => $form->createView(),
            'membre' => $membre
        ]);
    }

    /**
     * @Route("/{urlPublic}/article/editer", name="editArticle")
     * @param Article $article
     * @param Request $request
     * @param MembresRepository $membresRepository
     * @return Response
     * @throws \Exception
     */
    public function editArticle(Communaute $communaute, Request $request, MembresRepository $membresRepository, ArticleRepository $repository) {


        $article = $repository->find($_GET['id']);
        $user = $this->getUser();
        if($user == null){
            return $this->redirect('/'.$communaute->getUrlPublic().'/articles');
        }
        $communaute = $article->getCommunaute();
        $membre = $membresRepository->findOneBy(['user' => $user, 'communaute' => $communaute]);
        if ($communaute->getUser() != $user) {
            if ($membre == null || $membre->getRole()->getDroitsUtilisateur()->getModification() != 1)
                return $this->redirectToRoute($this->redirectToRoute('showArticles', ['urlPublic'=>$communaute->getUrlPublic()]));
        }
        $form = $this->createFormBuilder($article)
            ->add('titre', TextType::class, [
                "required" => true,
                'attr' => ["placeholder" => "titre"]
            ])
            ->add('contenu', TextareaType::class, [
                "required" => true,
            ])
            ->add('introduction', TextareaType::class, [
                "required" => true,
                "attr" => ["placeholder" => "introduction"]
            ])
            ->add('categories', EntityType::class, [
                'class' => Categories::class,
                'query_builder' => function (CategoriesRepository $cr) use ($article) {
                    return $cr->createQueryBuilder('c')
                        ->where('c.communaute = :comm')
                        ->setParameter('comm', $article->getCommunaute());
                },
                'choice_label' => 'nom',
                'expanded' => false
            ])
            ->getForm();

        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()){
            if (isset($_FILES)) {
                $extensions_autorisees = array('jpg', 'jpeg', 'gif', 'png', 'PNG', 'JPG', 'JPEG', 'GIF');
                if ($_FILES['image'] and $_FILES['image']['error'] == 0) {
                    $image = pathinfo($_FILES['image']['name']);
                    $extension = $image['extension'];
                    if (in_array($extension, $extensions_autorisees)) {
                        $nameFile = $image['filename'];
                        while (file_exists('articles/' . $nameFile . '.' . $extension)) {
                            $nameFile = $nameFile . '-copy';
                        }
                        if ($article->getImage() != null)
                            unlink('articles/'.$article->getImage());
                        move_uploaded_file($_FILES['image']['tmp_name'], 'articles/' . $nameFile . '.' . $extension);
                        $article->setImage($nameFile . '.' . $extension);
                    }
                }
                $article->setUser($user);
                $article->setCommunaute($communaute);
                $article->setCreatedAt(new \DateTime());
                $this->em->persist($article);
                $this->em->flush();
                $this->addFlash('success','Votre artilce est bien modifié');


                return $this->redirectToRoute('userShowArticle', ['urlPublic' => $communaute->getUrlPublic(), 'id' => $article->getId()]);
            }
        }

        return $this->render('user/editArticle.html.twig', [
            'communaute' => $communaute,
            'form' => $form->createView(),
            'membre' => $membre,
            'article' => $article,
        ]);
    }

    /**
     * @Route("/remove/article-{id}", name="removeArticle")
     * @param Request $request
     * @param Article $article
     * @return JsonResponse
     */
    public function removeArticle(Request $request, Article $article,PostRepository $postRepository) {

         $deletepostArticleId = $postRepository->find($article->getId());
                dd( $deletepostArticleId);
        if ($request->isXmlHttpRequest()) {
            $submittedToken = $request->request->get('_token');
            $result['result'] = 'error';
            if ($this->isCsrfTokenValid('remove'.$article->getId(), $submittedToken)) {
                $this->em->remove($article);
                $deletepostArticleId = $postRepository->findOneByArticleId($article->getId());
                
                $this->em->remove($deletepostArticleId);

                $this->em->flush();


                $result['result'] = 'success';
            }
            return new JsonResponse($result);
        }

        throw new NotFoundHttpException('La page n\'existe pas');
    }

    /**
     * @Route("/edit/comment-{id}.html", name="editComment")
     * @param Commentaires $commentaire
     * @param Request $request
     * @return JsonResponse
     */
    public function editComment(Commentaires $commentaire, Request $request) {
        if ($request->isXmlHttpRequest()) {
            $resp = 'error';
            if ('' != $request->request->get('value')) {
                $val = $request->request->get('value');
                $commentaire->setContenus($val);
                $this->em->persist($commentaire);
//                $this->em->flush();
                $resp = 'success';
            }
            return new JsonResponse(['result' => $resp]);
        }

        throw new NotFoundHttpException('La page n\'existe pas');
    }

    /**
     * @Route("/remove/comment-{id}.html", name="removeComment")
     * @param Commentaires $commentaire
     * @param Request $request
     * @return JsonResponse
     */
    public function removeComment(Commentaires $commentaire, Request $request) {
        if ($request->isXmlHttpRequest()) {
            $submittedToken = $request->request->get('_token');
            if ($this->isCsrfTokenValid('delete', $submittedToken)) {
                $this->em->remove($commentaire);
                $this->em->flush();
                return new JsonResponse(['result' => 'success']);
            }
            return new JsonResponse(['result' => 'failed']);
        }

        throw new NotFoundHttpException('La page n\'existe pas');
    }

    /**
     * @Route("/{urlPublic}/article", name="userShowArticle")
     * @param Communaute $communaute
     * @param Request $request
     * @param MembresRepository $membresRepository
     * @param ArticleRepository $articleRepository
     * @return Response
     */
    public function showArticle(Communaute $communaute, Request $request, MembresRepository $membresRepository, ArticleRepository $articleRepository) {
        $article = $articleRepository->find($_GET['id']);
        $user = $this->getUser();
        if($user == null){
            return $this->redirect('/'.$communaute->getUrlPublic().'/article?id='.$_GET['id']);
        }
        $membre = $membresRepository->findOneBy(['user' => $user, 'communaute' => $article->getCommunaute()]);
        return $this->render('user/showArticle.html.twig', [
            'article' => $article,
            'communaute' => $article->getCommunaute(),
            'membre' => $membre
        ]);
    }

    /**
     * @Route("/{urlPublic}/chat", name="chat")
     * @param Request $request
     * @return Response
     */
    public function chat(Request $request, Communaute $communaute, ChatRepository $repository)
    {
        $membre = $this->em->getRepository(Membres::class)->findOneBy(['user' => $this->getUser(), 'communaute' => $communaute]);
        return $this->render('user/chat.html.twig', [
            'ws_url' => 'localhost:8080',
            'communaute' => $communaute,
            'membre' => $membre
        ]);
    }

    /**
     * @Route("/chat/messages/communaute-{id}.html", name="_messages")
     * @param Request $request
     * @param Communaute $communaute
     * @param ChatRepository $chatRepository
     * @return Response
     */
    public function chatMessages(Request $request, Communaute $communaute, ChatRepository $chatRepository)
    {
        return $this->render('user/chatMessages.html.twig', [
            'messages' => $chatRepository->findByCommunaute($communaute)
        ]);
    }

    /**
     * @Route("/community/files-{id}.html", name="_files")
     * @param Request $request
     * @param Communaute $communaute
     * @return Response
     */
    public function filesCommunity(Request $request, Communaute $communaute)
    {
        $membre = $this->em->getRepository(Membres::class)->findOneBy(['communaute' => $communaute, 'user' => $this->getUser()]);
        return $this->render('user/filesList.html.twig', [
            'communaute' => $communaute,
            'membre' => $membre
        ]);
    }

    /**
     * @Route("/add/message", name="addMessage")
     * @param Request $request
     * @return JsonResponse|NotFoundHttpException
     * @throws \Exception
     */
    public function addMessage(Request $request, CommunauteRepository $repository){
        $user = $this->getUser();
        $chat = new Chat();
        $idComm = (int)$request->get('comm');
        $communaute = $repository->find($idComm);
        if($request->isXmlHttpRequest() && !empty($request->get('contenu'))){
            if(isset($_FILES)){
                $infos=[];
                $names=[];
                for($i=0; $i<sizeof($_FILES); $i++){
                    $infos [] = pathinfo($_FILES['f'.$i]['name']);
                    $names[] = $infos[$i]['basename'];
                    move_uploaded_file($_FILES['f'.$i]['tmp_name'], 'files/' . $names[$i]);
                }
                $chat->setFiles($names);
            }
            $chat->setCreatedAt(new \DateTime())
                ->setUser($user)->setCommunaute($communaute)
                ->setContenu($request->get('contenu'));
            $this->em->persist($chat);
            $this->em->flush();
            return new JsonResponse([
                    'created_at' => date_format($chat->getCreatedAt(), 'c'),
                    'nameFiles' => $names
                ]);
        } else {
            if(isset($_FILES) and $_FILES != []){
                $infos=[];
                $names=[];
                for($i=0; $i<sizeof($_FILES); $i++){
                    $infos[] = pathinfo($_FILES['f'.$i]['name']);
                    $names[] = $infos[$i]['basename'];
                    move_uploaded_file($_FILES['f'.$i]['tmp_name'], 'files/' . $names[$i]);
                }
                $chat->setFiles($names);
                $chat->setCreatedAt(new \DateTime())
                    ->setUser($user)->setCommunaute($communaute)
                    ->setContenu(null);
                $this->em->persist($chat);
                $this->em->flush();
                return new JsonResponse([
                        'created_at' => date_format($chat->getCreatedAt(), 'c'),
                        'nameFiles' => $names
                    ]);
            }
        }
        throw $this->createNotFoundException('La page n\'éxiste pas');
    }

    /**
     * @Route("/action/like-{id}", name="actionLike")
     * @param Request $request
     * @param Post $post
     * @param LikeRepository $likeRepository
     * @return JsonResponse
     */
    public function like(Request $request, Post $post, LikeRepository $likeRepository){
        if ($request->isXmlHttpRequest()) {
            $user = $this->getUser();
            $like = new Like();
            $text = "je n'aime plus";
            foreach ($post->getLikes() as $l) {
                if ($l->getUser() == $user) {
                    $like = $l;
                    break;
                }
            }
            if ($like->getId() != null) {
                $status = $like->getStatus();
                if ($status) {
                    $text = "j'aime";
                }
                $like->setStatus(!$status);
            } else {
                $like->setUser($user);
                $like->setPost($post);
                $like->setStatus(true);
            }
            $this->em->persist($like);
            $this->em->flush();

            $likea = $likeRepository->findId($post);
            // dd(sizeof($likea));
            // $count = 0;
            if(sizeof($post->getLikes()) == 0){
                $count = 1;
            }else{
                $count = sizeof($likea);
            }
            return new JsonResponse(['text' => $text, 'count' => $count]);
        }
        throw new NotFoundHttpException('La page n\'éxiste pas');
    }

    /**
     * @Route("/recuper/message", name="recupermessage")
     * @param ChatRepository $repository
     * @return JsonResponse
     */
    public function RecuperMessage(ChatRepository $repository){
        $user = $this->getUser();
        $chat = $repository->findAll();
        $formatted = [];
        foreach ($chat as $messages){
            $formatted[] = [
                'id' => $messages->getId(),
                'contenu'=> $messages->getContenu(),
                'user'=> $messages->getUser()->getUsername(),
                'photo' => $messages->getUser()->getPhoto()
            ];
        }
            return new JsonResponse($formatted);
    }

    /**
     * @Route("/{urlPublic}/contact", name = "contactNous")
     * @param Request $request
     * @param Communaute $communaute
     * @param \Swift_Mailer $mailer
     * @return Response
     */
    public function contactNous(Request $request, Communaute $communaute, \Swift_Mailer $mailer) {
        $fondateur = $communaute->getUser();
        $user = $this->getUser();
        if($user == null){
            return $this->redirect('/'.$communaute->getUrlPublic().'/contact');
        }
        $membre = $this->em->getRepository(Membres::class)->findOneBy(['user' => $this->getUser(), 'communaute' => $communaute]);
        $message = new Message();
        $form = $this->createFormBuilder($message)
            ->add('contenu', TextareaType::class, ['attr' => ['placeholder' => 'Tapez votre message ici..']])->getForm();
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()){
            $this->addFlash('success', 'Votre message est bien envoyé!');
            $infos = $request->get('form');
            $message->setContenu($infos['contenu'])
                ->setUser($user);
            $this->em->persist($message);
            $this->em->flush();
            $email = (new \Swift_Message('Objet'))
                ->setFrom($user->getEmail())
                ->setTo(trim($fondateur->getEmail()))
                ->setBody(
                    $this->renderView('emails/contact.html.twig',[
                        'message'=>$infos['contenu']
                    ]),
                    'text/html'
                );
            $mailer->send($email);

            return $this->redirectToRoute('contactNous', ['urlPublic'=>$communaute->getUrlPublic()]);

        }
        return $this->render('user/message.html.twig',[
            'form' => $form->createView(),
            'communaute' => $communaute,
            'membre' => $membre
        ]);
    }

    /**
     * @Route("/contact/nous", name = "contactNousAccueil")
     * @param Request $request
     * @param \Swift_Mailer $mailer
     * @return Response
     */
    public function contactNousAccueil(Request $request, \Swift_Mailer $mailer){
        $user = $this->getUser();
        $message = new Message();
        $form = $this->createFormBuilder($message)->getForm();
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()){
            $this->addFlash('success', 'Votre message est bien envoyé!');
            $infos = $request->get('form');
            $message->setContenu($_POST['contenu'])
                ->setUser($user)
                ->setNameSender(isset($_POST['username'])?$_POST['username']:'');
            $this->em->persist($message);
            $this->em->flush();
            $email = (new \Swift_Message('Message TribADD : '.$_POST['objet']))
                ->setFrom($user->getEmail())
                ->setTo('contact@tribadd.com')
                ->setBody(
                    $this->renderView('emails/contact.html.twig',[
                        'message'=>$_POST['contenu']
                    ]),
                    'text/html'
                );
            $mailer->send($email);

            // return $this->redirectToRoute('contactNousAccueil');

        }
        return $this->render('user/message2.html.twig',[
            'form'=>$form->createView()
        ]);
    }

    /**
     * @Route("/{urlPublic}/membres", name = "showMembres")
     * @param Request $request
     * @param Communaute $communaute
     * @return Response
     */
    public function showMembres(Request $request, Communaute $communaute){
        $membre = $this->em->getRepository(Membres::class)->findOneBy(['user' => $this->getUser(), 'communaute' => $communaute]);
        if ($communaute->getUser() != $this->getUser())
        if ($membre == null || $membre->getRole()->getLibelle() != 'administrateur') {
            return $this->redirectToRoute('ShowmyCommunaute', ['urlPublic' => $communaute->getUrlPublic()]);
        }
        return $this->render('user/showMembres.html.twig', [
            'communaute' => $communaute,
            'membre' => $membre
        ]);
    }

    /**
     * @Route("/modification/droits-communaute-{id}.html", name="editDroits")
     * @param Request $request
     * @param Communaute $communaute
     * @param RolesRepository $rolesRepository
     * @return Response
     */
    public function editDroits(Request $request, Communaute $communaute, RolesRepository $rolesRepository) {
        if ($communaute->getUser() != $this->getUser())
            return $this->redirectToRoute('ShowmyCommunaute', ['urlPublic' => $communaute->getUrlPublic()]);
        $form = $this->createForm(CommunauteType::class, $communaute, ['libelle' => true]);
        $user = $this->getUser();
        $membre = $this->em->getRepository(Membres::class)->findOneBy(['user' => $user, 'communaute' => $communaute]);

        $form->handleRequest($request);
        if ($request->request->get('_token')) {
            $submittedToken = $request->request->get('_token');
            if ($this->isCsrfTokenValid('edit-' . $communaute->getId(), $submittedToken)) {
                if ($form->isSubmitted() && $form->isValid()) {
                    $this->em->persist($communaute);
                    $this->em->flush();
                    $this->addFlash('success', 'Les droits ont été enregistrés avec success');
                    return $this->redirectToRoute('editDroits', ['id' => $communaute->getId()]);
                }
            }
        }
        return $this->render('user/gestionRolesCommunity.html.twig', [
            'communaute' => $communaute,
            'form' => $form->createView(),
            'membre' => $membre
        ]);
    }

    /**
     * @Route("/change/role-{id}.html", name="change_role")
     * @param Communaute $communaute
     * @param Request $request
     * @return Response
     */
   public function changeRole(Communaute $communaute, Request  $request)
    {
        $user = $this->em->getRepository(User::class)->find($request->query->get('user'));
        $role = $this->em->getRepository(Roles::class)->findRoleCommunity($communaute, $user);
        $roles = $this->em->getRepository(Roles::class)->findBy(['communaute' => $communaute]);
        if (isset($_POST['role'])) {
            $role = $this->em->getRepository(Roles::class)->findOneBy(['libelle' => $_POST['role']]);
            $membre = $this->em->getRepository(Membres::class)->findOneBy(['user' => $user, 'communaute' => $communaute]);
            $membre->setRole($role);
            $this->em->persist($membre);
            $this->em->flush();
            return $this->redirectToRoute('showMembres', ['urlPublic' => $communaute->getUrlPublic()]);
        }
        return $this->render('user/changeRole.html.twig', [
            'user' => $user,
            'communaute' => $communaute,
            'role' => $role,
            'roles' => $roles
        ]);
    }

    /**
     * @Route("/profile/affiche", name="myProfile")
     * @param Request $request
     * @param UserRepository $userRepository
     * @return Response
     */
    public function myProfile(Request $request, UserRepository $userRepository) {
        $user = $this->getUser();
        $user = $userRepository->findOneBy(['username' => $user->getUsername()]);
        $form = $this->createFormBuilder($user)
            ->add('nom', TextType::class, ['attr' => ['placeholder' => 'Nom'],'required' => false])
            ->add('prenom', TextType::class,['label' => 'prénom','attr' => ['placeholder' => 'Prénom'],'required' => false])
            ->add('adresse',TextType::class, ['attr' => ['placeholder' => 'Adresse'],'required' => false])
            ->add('dateNaissance', DateType::class, ['label' => 'Date de naissance', 'widget' => 'single_text' ,'attr' => ['placeholder' => 'Votre date de naissance'], 'required' => false])
            ->add('siteWeb', TextType::class, ['attr' => ['placeholder' => 'Site web'], 'required' => false])
            ->add('description', TextareaType::class, ['label' => "description", 'attr' => ['placeholder' => 'Description'], 'required' => false])
            ->add('telephone', TextType::class, ['label' => 'Numéro mobile', 'attr' => ['placeholder' => 'Numéro mobile'], 'required' => false])
            ->getForm();
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->em->persist($user);
            $this->em->flush();
            $this->addFlash('success', 'Votre profil a été modifié avec succès!');
            return $this->redirectToRoute('myProfile');
        }
        if (!empty($_FILES)) {
            if ($_FILES['photoProfil'] and $_FILES['photoProfil']['error'] == 0) {
                $exFileName = $user->getPhoto();
                $image = pathinfo($_FILES['photoProfil']['name']);
                $extension = $image['extension'];
                $extensions_autorisees = array('jpg', 'jpeg', 'gif', 'png', 'PNG', 'JPG', 'JPEG', 'GIF');
                if (in_array($extension, $extensions_autorisees)) {
                    $permitted_chars = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
                    function generate_string($input, $strength = 16) {
                        $input_length = strlen($input);
                        $random_string = '';
                        for($i = 0; $i < $strength; $i++) {
                            $random_character = $input[mt_rand(0, $input_length - 1)];
                            $random_string .= $random_character;
                        }

                        return $random_string;
                    }
                    $nameFile = generate_string($permitted_chars, 40) . '.' . strtolower($extension);
                    if ($exFileName != null)
                    unlink('photo_de_profil/' . $exFileName);
                    move_uploaded_file($_FILES['photoProfil']['tmp_name'], 'photo_de_profil/' . $nameFile);
                    $user->setPhoto($nameFile);
                    $this->em->persist($user);
                    $this->em->flush();
                }
                $this->addFlash('success', 'Votre photo de profil a été changé avec succès!');
                return $this->redirectToRoute('myProfile');
            }
        }
        return $this->render('user/userProfil.html.twig', [
            'user' => $user,
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/{urlPublic}/videos", name="indexVideo")
     * @param Communaute $communaute
     * @param MembresRepository $repositoryMembre
     * @return Response
     */
    public function indexVideo(Communaute $communaute, MembresRepository $repositoryMembre){
        $user = $this->getUser();
        if($user == null){
            return $this->redirect('/'.$communaute->getUrlPublic().'/videos');
        }
        $membre = $repositoryMembre->findOneBy(['user' => $user, 'communaute' => $communaute]);
        return $this->render('user/video.html.twig',[
            'communaute'=>$communaute,
            'membre' => $membre,
        ]);
    }

    /**
     * @Route("/{urlPublic}/nouvelle/video", name = "newVideo")
     * @param Request $request
     * @param Communaute $communaute
     * @param MembresRepository $repositoryMembre
     * @return Response
     * @throws \Exception
     */
    public function newVideo(Request $request, Communaute $communaute, MembresRepository $repositoryMembre){
        $user = $this->getUser();
        $membre = $repositoryMembre->findOneBy(['user' => $user, 'communaute' => $communaute]);
        if ($communaute->getUser() != $user) {
            if ($membre == null || $membre->getRole()->getDroitsUtilisateur()->getCreation() != 1)
                return $this->redirectToRoute($this->redirectToRoute('indexVideo', ['id' => $communaute->getId()]));
        }
        $video = new Video();
        $post = new Post();
        $form = $this->createFormBuilder($video)
            ->add('titre', TextType::class)
            ->add('description', TextareaType::class)->getForm();
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){
            $info = $request->get('form');
            $extensions_autorisees = array('mp4', 'webm', 'flv', 'avi', 'MP4', 'WEBM', 'FLV', 'AVI');
            $extensions_auto = array('jpg', 'jpeg', 'gif', 'png', 'PNG', 'JPG', 'JPEG', 'GIF');
            if ($_FILES['video'] and $_FILES['video']['error'] == 0) {

                $fichierVideo = pathinfo($_FILES['video']['name']);
                $extension = $fichierVideo['extension'];

                if (in_array($extension, $extensions_autorisees)) {
                    // dd($request);
                    $nameFile = $fichierVideo['filename'];
                    // dd($nameImage);
                    while (file_exists('videos/' . $nameFile . '.' . $extension)) {
                        $nameFile = $nameFile . '-copy';
                    }
                    move_uploaded_file($_FILES['video']['tmp_name'], 'videos/' . $nameFile.'.'.$extension);
                    if($_FILES['image'] and $_FILES['image']['error'] == 0)
                    {
                        $fichierImage = pathinfo($_FILES['image']['name']);
                        $extensionImage = $fichierImage['extension'];
                        if(in_array($extensionImage, $extensions_auto)){
                            $nameImage = $fichierImage['filename'];
                            while (file_exists('miniature/' . $nameImage . '.' . $extensionImage)) {
                                $nameImage = $nameImage . '-copy';
                            }
                            move_uploaded_file($_FILES['image']['tmp_name'], 'miniature/' . $nameImage.'.'.$extensionImage);
                            $post->setImageEncadre($nameImage.'.'.$extensionImage);
                        }
                    }else{
                        $post->setImageEncadre('video.jpg');
                        $nameImage = 'video.jpg';
                    }
                    $video->setFichier($nameFile.'.'.$extension)->setUser($user)->setCommunaute($communaute)->setCreatedAt(new \DateTime())->setImage($nameImage);
                    $nameFile = $fichierVideo['filename'];
                    $nameImage = $fichierImage['filename'];

                    $video->setFichier($nameFile . '.' . $extension)->setUser($user)->setCommunaute($communaute)->setCreatedAt(new \DateTime())->setImage($nameImage.'.'.$extensionImage);

                    $this->em->persist($video);
                    $this->em->flush();

                    $post->setUser($user)->setCommaunaute($communaute)->setTitre('Nouvelle vidéo : '.$info['titre'])->setArticleId($video->getId())->setShortDescription($info['description'])->setPublishedAt(new \DateTime())
                        ->setType('V');

                    $this->em->persist($post);
                    $this->em->flush();
                    return $this->redirectToRoute('indexVideo',['urlPublic'=>$communaute->getUrlPublic()]);
                }
            }
        }
        return $this->render('user/newvideo.html.twig',[
            'communaute'=>$communaute,
            'membre' => $membre,
            'form'=>$form->createView()
        ]);
    }

    /**
     * @Route("/{urlPublic}/video", name="viewVideo")
     * @param Communaute $communaute
     * @param VideoRepository $videoRepository
     * @param VideoRepository $repository
     * @param MembresRepository $repositoryMembre
     * @return Response
     */
    public function showVideo(Communaute $communaute,VideoRepository $videoRepository, VideoRepository $repository, MembresRepository $repositoryMembre){
        $video = $videoRepository->find($_GET['id']);
        $communaute = $video->getCommunaute();
        $user = $this->getUser();
        if($user == null){
            return $this->redirect('/'.$communaute->getUrlPublic().'/video?id='.$_GET['id']);
        }
        $isMembre = $repositoryMembre->findOneBy(['communaute' => $communaute, 'user' => $user]);
        // $val = sizeof($isMembre);
        $Videoss = $repository->findVideo($communaute, $video->getFichier());

        return $this->render('user/showVideo.html.twig',[
            'video'=>$video,
            'communaute'=>$communaute,
            'others'=>$Videoss,
            'membre' => $isMembre,
        ]);
    }

    /**
     * @Route("/{urlPublic}/block-user", name="blockedMember")
     * @param Request $request
     * @param Communaute $communaute
     * @param UserRepository $userRepository
     * @return JsonResponse
     */
    public function blockedMember(Request $request, Communaute $communaute, UserRepository $userRepository)
    {
        if ($request->isXmlHttpRequest()) {
            $user = $userRepository->find($request->query->get('user'));
            $response = false;
            $membre = $this->em->getRepository(Membres::class)->findOneBy(['communaute' => $communaute, 'user' => $user]);
            if ($membre) {
                $membre->setRole(null);
                $this->em->persist($membre);
                $this->em->flush();
                $response = true;
            }
            return $this->json([
                "response" => $response
            ]);
        }

        throw new RouteNotFoundException('La page est introuvable');
    }

    /**
     * @Route("/{urlPublic}/unblock-user", name="unblockedMember")
     * @param Request $request
     * @param Communaute $communaute
     * @param UserRepository $userRepository
     * @return JsonResponse
     */
    public function unblockedMember(Request $request, Communaute $communaute, UserRepository $userRepository)
    {
        if ($request->isXmlHttpRequest()) {
            $user = $userRepository->find($request->query->get('user'));
            $response = false;
            $membre = $this->em->getRepository(Membres::class)->findOneBy(['communaute' => $communaute, 'user' => $user]);
            $role = $this->em->getRepository(Roles::class)->findOneBy(['communaute' => $communaute, 'libelle' => 'membre']);
            if ($membre) {
                $membre->setRole($role);
                $this->em->persist($membre);
                $this->em->flush();
                $response = true;
            }
            return $this->json([
                "response" => $response
            ]);
        }

        throw new RouteNotFoundException('La page est introuvable');
    }

    /**
     * @Route("/{urlPublic}/remove-user", name="removedMember")
     * @param Request $request
     * @param UserRepository $userRepository
     * @param Communaute $communaute
     * @return JsonResponse
     */
    public function removeMember(Request $request, UserRepository $userRepository, Communaute $communaute)
    {
        if ($request->isXmlHttpRequest()) {
            $user = $userRepository->find($request->query->get('user'));
            $response = false;
            $membre = $this->em->getRepository(Membres::class)->findOneBy(['communaute' => $communaute, 'user' => $user]);
            if ($membre) {
                $this->em->remove($membre);
//                $this->em->flush();
                $response = true;
            }

            return $this->json([
                "response" => $response
            ]);
        }

        throw new RouteNotFoundException('La page est introuvable');
    }

    /**
     * @Route("/{urlPublic}/documents", name="downloadSpace")
     * @param Communaute $communaute
     * @param Request $request
     * @return Response|JsonResponse
     * @throws \Exception
     */
    public function espaceTelechargement(Communaute $communaute, Request $request) {
        $user = $this->getUser();
        $membre = $this->em->getRepository(Membres::class)->findOneBy(['communaute' => $communaute, 'user' => $user]);
        if ($request->isXmlHttpRequest()) {
            function typeFile($ext) {
                if (in_array(strtolower($ext), ['doc', 'docx']))
                    return 'word';
                elseif (in_array(strtolower($ext), ['xls', 'xlsx']))
                    return 'excel';
                elseif (in_array(strtolower($ext), ['ppt', 'pptx']))
                    return 'powerpoint';
                elseif (strtolower($ext) == 'pdf')
                    return 'pdf';
                elseif (strtolower($ext) == 'zip')
                    return 'archive';
                elseif (strtolower($ext) == 'txt')
                    return 'text';
                elseif (in_array(strtolower($ext), ['avi', 'mpg', 'mkv', 'mov', 'mp4', '3gp', 'webm', 'wmv']))
                    return 'video';
                elseif (in_array(strtolower($ext), ['mp3', 'wav']))
                    return 'audio';
                elseif (in_array(strtolower($ext), ['jpg', 'jpeg', 'gif', 'png']))
                    return 'image';
                elseif (in_array(strtolower($ext), ['php', 'js', 'css', 'htm', 'html']))
                    return 'web';
                else
                    return true;
            }
            if ($request->files->get('file')) {
                $pathPackage = new PathPackage(
                    '/upload-files',
                    new EmptyVersionStrategy());
                $preview = $config = $errors = [];
                $files = $request->files->get('file');
                foreach ($files as $file)
                {
                    $downSpace = new DownloadSpace();
                    $extension = $file->guessExtension();
                    $filename = md5(uniqid()).'.'.$extension;
                    $originalName = $file->getClientOriginalName();
                    $fileSize = $file->getSize();
                    if ($fileSize > 5000000)
                        return new JsonResponse(['error' => 'Le fichier "'.$originalName.'" ('. $fileSize .' Ko) dépasse la taille maximale autorisée qui est de 5000 Ko']);
                    /*if (!in_array($extension, ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'zip', 'mp3', 'wav', 'txt']))
                        return new JsonResponse(['error' => 'Extension invalide pour le fichier "'.$originalName.'". Seules les extensions "pdf, doc, docx, xls, xlsx, ppt, pptx, zip, mp3, wav, txt" sont autorisées.']);*/
                    if ($file->move('upload-files/', $filename)) {
                        $downSpace->setFile($filename)
                            ->setFileOriginName($originalName)
                            ->setFileSize($fileSize)
                            ->setUser($user)
                            ->setCommunaue($communaute)
                            ->setCreatedAt(new \DateTime());
                        $this->em->persist($downSpace);
                        $fileId = str_replace('.' . $extension, '', $filename);
                        $newFileUrl = $pathPackage->getUrl($filename);
                        $preview[] = $newFileUrl;
                        $config[] = [
                            'key' => $fileId,
                            'caption' => $originalName,
                            'type' => typeFile($extension),
                            'size' => $fileSize,
                            'downloadUrl' => $newFileUrl, // the url to download the file
                            'url' => $this->generateUrl('deleteFile', ['id' => $fileId]),
                        ];
                    } else {
                        $errors[] = $originalName;
                    }
                }
                $this->em->flush();
                $out = ['showPreview' => false,'initialPreview' => $preview, 'initialPreviewConfig' => $config, 'initialPreviewAsData' => true];
                if (!empty($errors)) {
                    $file = count($errors) === 1 ? 'file "' . $errors[0]  . '" ' : 'files: "' . implode('", "', $errors) . '" ';
                    $out['error'] = 'Oh zut! L\'exportation de ' . $file . 'a échoué. Veuillez réessayer plus tard.';
                }
                return new JsonResponse($out);
            }
            return new JsonResponse(['error' => "Erreur serveur."]);
        }
        return $this->render('user/espaceTelechargement.html.twig', [
            'communaute' => $communaute,
            'membre' => $membre
        ]);
    }

    /**
     * @Route("/delete-file/{id}", name="deleteFile")
     * @param Request $request
     * @param String $id
     * @param DownloadSpaceRepository $repository
     * @return JsonResponse
     */
    public function deleteFile(Request $request, String $id, DownloadSpaceRepository $repository) {
        $file = $repository->findLikeFilename($id);
        if ($file == null) return new JsonResponse([]);
        if (unlink('upload-files/'.$file['file'])) {
            $this->em->remove($repository->find($file['id']));
            $this->em->flush();
        }
        return new JsonResponse([]);
    }

    /**
     * @Route("/remove/file/{id}", name="removeFile")
     * @param Request $request
     * @param DownloadSpace $file
     * @return JsonResponse
     */
    public function removeFile(Request $request, DownloadSpace $file) {
        if ($request->isXmlHttpRequest()) {
            $result['result'] = 'error';
            if (unlink('upload-files/'.$file->getFile())) {
                $this->em->remove($file);
                $this->em->flush();
                $result['result'] = 'success';
            }
            return new JsonResponse($result);
        }

        throw new NotFoundHttpException('La page n\'existe pas');
    }

    /**
     * @Route("/recherche/communautes", name="recherce")
     * @param Request $request
     * @param CommunauteRepository $repository
     * @return Response
     */
    public function recherce(Request $request, CommunauteRepository $repository){
        if(isset($_POST['mot-cle'])){
            $resultat = $repository->recherche($_POST['mot-cle']);
            $autre = $repository->autreResultat($_POST['mot-cle']);
            return $this->render('user/recherche.html.twig',[
                'resultats'=>$resultat,
                'mot'=>$_POST['mot-cle']
            ]);
        }else{
            return $this->redirectToRoute('user');
        }
    }

    /**
     * @Route("/{urlPublic}/forum-reponse", name="showForum")
     * @param Communaute $communaute
     * @param ForumRepository $forumRepository
     * @param Request $request
     * @param UserRepository $userRepository
     * @param MembresRepository $repositoryMembre
     * @param ForumReponseRepository $frRepository
     * @return Response
     */
    public function showForum(Communaute $communaute, ForumRepository $forumRepository, Request $request, UserRepository $userRepository,MembresRepository $repositoryMembre,ForumReponseRepository $frRepository) {
        $user = $this->getUser();
          $fr= new ForumReponse();

          $forum = $forumRepository->find($request->query->get('id'));
        $communaute = $forum->getCommunaute();
            $forums = $this->em->getRepository(Forum::class)->findBy(['communaute' => $communaute]);
        $membre = $repositoryMembre->findOneBy(['user' => $user, 'communaute' => $communaute]);
          $frs = $this->em->getRepository(ForumReponse::class)->findBy(['forum' => $forum->getId()]);
         $count = $frRepository->nbCount($forum);

         $form = $this->createFormBuilder($fr)
            ->add('contenu', TextareaType::class)
        ->getForm();

        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()){
            $fr->setCreateAt(new \DateTime());
            $fr->setUser($user);
            $fr->setForum($forum);
            $this->em->persist($fr);
            $this->em->flush();
            return $this->redirectToRoute('showForum', ['urlPublic' => $communaute->getUrlPublic(),'id' => $forum->getId()]);
        }
        return $this->render('user/showForum.html.twig', [
            'forums' => $forum,
            'membre' => $membre,
            'communaute' => $communaute,
            'frs'=> $frs,
            'count' => $count,
             'form' => $form->createView()
        ]);
    }


    /**
     * @Route("/{urlPublic}/forum", name="forum")
     * @param Communaute $communaute
     * @param Request $request
     * @param UserRepository $userRepository
     * @param MembresRepository $repositoryMembre
     * @return Response
     * @throws \Exception
     */
    /*public function forum(Communaute $communaute, Request $request, UserRepository $userRepository,MembresRepository $repositoryMembre) {
        $forum= new Forum();
        $forums = $this->em->getRepository(Forum::class)->findBy(['communaute' => $communaute]);
        $user = $this->getUser();
        /*if($user == null){
            return $this->redirect('/'.$communaute->getUrlPublic());
        }*/
        /*$membre = $repositoryMembre->findOneBy(['user' => $user, 'communaute' => $communaute]);
        $form = $this->createFormBuilder($forum)
            ->add('titre', TextType::class)
            ->add('contenu', TextareaType::class)
        ->getForm();
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()){
            $forum->setCreateAt(new \DateTime());
            $forum->setUser($user);
            $forum->setCommunaute($communaute);
            $forum->setStatus(true);
            $this->em->persist($forum);
            $this->em->flush();
            return $this->redirectToRoute('forum', ['urlPublic' => $communaute->getUrlPublic()]);
        }

        return $this->render('user/gestionForum.html.twig', [
            'communaute' => $communaute,
            'membre' => $membre,
            'forums' => $forums,
            'form' => $form->createView()
        ]);

    }*/
    public function forum(Communaute $communaute, Request $request, UserRepository $userRepository,MembresRepository $repositoryMembre) {
        $forum= new Forum();
        $forums = $this->em->getRepository(Forum::class)->findBy(['communaute' => $communaute]);
        $user = $this->getUser();
        $membre = $repositoryMembre->findOneBy(['user' => $user, 'communaute' => $communaute]);
        $form = $this->createFormBuilder($forum)
            ->add('titre', TextType::class)
            ->add('contenu', TextareaType::class)
        ->getForm();
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()){
            $forum->setCreateAt(new \DateTime());
            $forum->setUser($user);
            $forum->setCommunaute($communaute);
            $forum->setStatus(true);
            $this->em->persist($forum);
            $this->em->flush();
            return $this->redirectToRoute('forum', ['urlPublic' => $communaute->getUrlPublic()]);
        }
        return $this->render('user/gestionForum.html.twig', [
            'communaute' => $communaute,
            'membre' => $membre,
            'forums' => $forums,
            'form' => $form->createView()
        ]);
    }
   

    /**
     * @Route("/remove/forum-repondre-{id}", name="removeForumRepondre")
     * @param Request $request
     * @param Forum $forum
     * @return JsonResponse
     */
    public function removeForumRepondre(Request $request, ForumReponse $forumRepondre) {
        if ($request->isXmlHttpRequest()) {
            $submittedToken = $request->request->get('_token');
            $result['result'] = 'error';
            if ($this->isCsrfTokenValid('remove'.$forumRepondre->getId(), $submittedToken)) {
                $this->em->remove($forumRepondre);
                $this->em->flush();
                $result['result'] = 'success';
            }
            return new JsonResponse($result);
        }
        throw new NotFoundHttpException('La page n\'existe pas');
    }

     /**
     * @Route("/remove/forum-{id}", name="removeForum")
     * @param Request $request
     * @param Forum $forum
     * @return JsonResponse
     */
    public function removeForum(Request $request, Forum $forum) {
        if ($request->isXmlHttpRequest()) {
            $submittedToken = $request->request->get('_token');
            $result['result'] = 'error';
            if ($this->isCsrfTokenValid('remove'.$forum->getId(), $submittedToken)) {
                $this->em->remove($forum);
                $this->em->flush();
                $result['result'] = 'success';
            }
            return new JsonResponse($result);
        }
        throw new NotFoundHttpException('La page n\'existe pas');
    }

    /**
     * @Route("/bloque/forum-{id}", name="bloqueForumRepondre")
     * @param Request $request
     * @param Forum $forum
     * @return JsonResponse
     */
    public function bloqueForumRepondre(Request $request, Forum $forum) {
        if ($request->isXmlHttpRequest()) {
            $result['result'] = 'error';
            $forum->setStatus(false);
              $this->em->persist($forum);
             $this->em->flush();
               $result['result'] = 'success';
            return new JsonResponse($result);
        }
        throw new NotFoundHttpException('La page n\'existe pas');
    }

     /**
     * @Route("/debloque/forum-{id}", name="debloqueForumRepondre")
     * @param Request $request
     * @param Forum $forum
     * @return JsonResponse
     */
    public function debloqueForumRepondre(Request $request, Forum $forum) {
        if ($request->isXmlHttpRequest()) {
            $result['result'] = 'error';
            $forum->setStatus(true);
              $this->em->persist($forum);
             $this->em->flush();
               $result['result'] = 'success';
            return new JsonResponse($result);
        }
        throw new NotFoundHttpException('La page n\'existe pas');
    }
    
    /**
     * @Route("/condition/general", name = "cguAccueil")
     * @param Request $request
     * @return Response
     */
    public function cguAccueil(Request $request){
        $user = $this->getUser();
       
        return $this->render('user/cgu.html.twig', [
            'user' => $user
        ]);
    }


    /**
     * @Route("/{urlPublic}/condition/generale", name="cguCommunaute")
     * @param Communaute $communaute
     * @param Request $request
     * @param MembresRepository $repositoryMembre
     * @return Response
     */
    public function cguCommunaute(Communaute $communaute, Request $request, MembresRepository $repositoryMembre){
        $user = $this->getUser();
        $membre = $repositoryMembre->findOneBy(['user' => $user, 'communaute' => $communaute]);
        return $this->render('user/cguCommunaute.html.twig', [
            'communaute' => $communaute,
            'membre' => $membre
        ]);
    }

    /**
     * @param Communaute $communaute
     * @Route("/{urlPublic}/newsletter", name="newsletter")
     * @return Response
     */
    public function indexNewsLetter(Communaute $communaute, MembresRepository $repository){
        $user = $this->getUser();
        $membre = $repository->findOneBy(['user' => $user, 'communaute' => $communaute]);
        return $this->render('user/newsletter.html.twig',[
            'communaute'=>$communaute,
            'membre'=>$membre
        ]);
    }
    /**
     * @param Communaute $communaute
     * @Route("/{urlPublic}/add-newsletter", name="addNewsletter")
     * @return Response
     */
    public function addNewsLetter(Communaute $communaute, Request $request, MembresRepository $repository){
        $user = $this->getUser();
        $membre = $repository->findOneBy(['user' => $user, 'communaute' => $communaute]);
        $newsLetter = new Newsletter();
        $form = $this->createForm(NewsLetterType::class, $newsLetter);
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()){
            if(isset($_POST['enreg'])){
                $newsLetter->setCommunaute($communaute)->setIsSended(false);
                $this->em->persist($newsLetter);
                $this->em->flush();
                return $this->redirectToRoute('newsletter', ['urlPublic'=>$communaute->getUrlPublic()]);
            }elseif(isset($_POST['addpost'])){
                $newsLetter->setCommunaute($communaute)->setIsSended(false);
                $this->em->persist($newsLetter);
                $this->em->flush();
                return $this->redirectToRoute('addpostNewsLetter', ['urlPublic'=>$communaute->getUrlPublic(), 'id'=>$newsLetter->getId()]);
            }else{
                $newsLetter->setCommunaute($communaute)->setIsSended(true);
                $this->em->persist($newsLetter);
                $this->em->flush();
                return $this->redirectToRoute('sendNewsLetter', ['urlPublic'=>$communaute->getUrlPublic(), 'id'=>$newsLetter->getId()]);
            }
        }
        return $this->render('user/addNewsletter.html.twig',[
            'communaute'=>$communaute,
            'form'=>$form->createView(),
            'membre'=>$membre
        ]);
    }

    /**
     * @param Communaute $communaute
     * @param Request $request
     * @param NewsletterRepository $letterRepository
     * @return Response
     * @Route("/{urlPublic}/add/post", name="addpostNewsLetter")
     */
    public function addPostNewsLetter(Communaute $communaute, Request $request, NewsletterRepository $letterRepository, MembresRepository $repository){
        $user = $this->getUser();
        $membre = $repository->findOneBy(['user' => $user, 'communaute' => $communaute]);
        $id = (int)$_GET['id'];
        $newsLetter = $letterRepository->find($id);
        $arrauVide = [];
        if(isset($_POST['checkPost'])){
            foreach($_POST as $post){
                if($post != 'ajouter') $arrauVide[] = $post;
            }
            $newsLetter->setIdPosts($arrauVide);
            $this->em->persist($newsLetter);
            $this->em->flush();
            return $this->redirectToRoute('newsletter', ['urlPublic'=>$communaute->getUrlPublic()]);
        }
        return $this->render('user/addPostNewsLetter.html.twig',[
            'communaute'=>$communaute,
            'membre'=>$membre
        ]);
    }

    /**
     * @param Request $request
     * @param Communaute $communaute
     * @param NewsletterRepository $repository
     * @return Response
     * @Route("/{urlPublic}/edit/newsletter", name="editnewsletter")
     */
    public function editNewsLetter(Request $request, Communaute $communaute, NewsletterRepository $repository, PostRepository $postRepository){
        $newsletter = $repository->find($_GET['id']);
        $post = [];
        $postnotcheck = [];
        $p = $postRepository->findBy(['communaute'=>$communaute]);
        for($i = 0; $i<sizeof($newsletter->getIdPosts()); $i++){
            $post [] = $postRepository->find($newsletter->getIdPosts()[$i]);
        }

        for($i=0; $i < sizeof($p); $i++){
            if(in_array($p[$i], $post)){

            }else{
                $postnotcheck [] = $p[$i];
            }
        }
        $form = $this->createForm(NewsLetterType::class, $newsletter);
        $form->handleRequest($request);
        $arrauVide = [];
        if(isset($_POST['checkPost'])){
            foreach($_POST as $post){
                if($post != 'Modifier' and $post != 'Modifier et Envoyer' and !is_array($post)) $arrauVide[] = $post;
            }
            if($_POST['checkPost'] == "Modifier"){
                $newsletter->setIdPosts($arrauVide);
                $this->em->persist($newsletter);
                $this->em->flush();
                return $this->redirectToRoute('newsletter', ['urlPublic'=>$communaute->getUrlPublic()]);
            }else{
                $newsletter->setIdPosts($arrauVide);
                $this->em->persist($newsletter);
                $this->em->flush();
                return $this->redirectToRoute('sendNewsLetter', ['urlPublic'=>$communaute->getUrlPublic(),'id'=>$newsletter->getId()]);
            }

        }
        return $this->render('user/editNewsLetter.html.twig',[
            'form'=>$form->createView(),
            'communaute'=>$communaute,
            'postChecke'=>$post,
            'postNotChecke'=>$postnotcheck,
        ]);
    }

    /**
     * @Route("/{urlPublic}/list/abonnee", name="listAbonnee")
     * @param Communaute $communaute
     * @return Response
     */
    public function listAbonnee(Communaute $communaute){
        $users = [];
        foreach ($communaute->getMembres() as $member){
            if($member->getAbonneNewsLetter()){
                $users [] = $member->getUser();
            }
        }
        return $this->render('user/listAbonnee.html.twig',[
            'users'=>$users,
            'communaute'=>$communaute
        ]);
    }

    /**
     * @param \Swift_Mailer $mailer
     * @param Request $request
     * @param Communaute $communaute
     * @param NewsletterRepository $repository
     * @Route("/{urlPublic}/send-newsEmail/letter/envoie", name="sendNewsLetter")
     * @return Response
     */
    public function sendNewsLetter(\Swift_Mailer $mailer, Request $request, Communaute $communaute, NewsletterRepository $repository, PostRepository $postRepository, RouterInterface $router){
        $newsletter = $repository->find($_GET['id']);
        $newsletter->setIsSended(true);
        $this->em->persist($newsletter);
        $this->em->flush();
        $idPost = $newsletter->getIdPosts();
        $post = [];
        for($i = 0; $i< sizeof($idPost); $i++){
            $post[] = $postRepository->find((int)$idPost[$i]);
        }
        $users = [];
        foreach ($communaute->getMembres() as $member){
            if($member->getAbonneNewsLetter()){
                $users [] = $member->getUser();
            }
        }
        $url = 'https://'.$router->getContext()->getHost();
        foreach ($users as $user){
            $message = (new \Swift_Message($newsletter->getObject()))
                ->setFrom($communaute->getUser()->getEmail())
                ->setTo(trim($user->getEmail()))
                ->setBody($this->renderView('emails/newsLetters.html.twig',[
                    'contenu'=>$newsletter->getContenu(),
                    'posts'=>$post
                ]));
            $mailer->send($message);
        }
        return $this->redirectToRoute('newsletter',['urlPublic'=>$communaute->getUrlPublic()]);
    }

    /**
     * @param Communaute $communaute
     * @param NewsletterRepository $repository
     * @Route("/{urlPublic}/detail/newsLetter", name="detailNewsLetter")
     * @return Response
     */
    public function detailNewsLetter(Communaute $communaute, NewsletterRepository $repository, MembresRepository $membresRepository, PostRepository $postRepository){
        $user = $this->getUser();
        $membre = $membresRepository->findOneBy(['user' => $user, 'communaute' => $communaute]);
        $newsLetter = $repository->find($_GET['id']);
        $arr = [];
        for($i = 0; $i < sizeof($newsLetter->getIdPosts()); $i++ ){
            $arr[] = $postRepository->find($newsLetter->getIdPosts()[$i]);
        }
        return $this->render('user/detaiNewsLetter.html.twig', [
            'n'=>$newsLetter,
            'communaute' => $communaute,
            'membre' => $membre,
            'post'=>$arr
        ]);
    }

     /**
     * @Route("/liste/membre-par-communaute", name = "listComMembre")
     * @param CommunauteRepository $repository
     * @return RedirectResponse|Response
     */
    public function listindex(Request $request,CommunauteRepository $repository,MembresRepository $repositoryMembre)
    {
     
        if ($this->getUser() == null) {
            return $this->redirectToRoute('login');
        } else {
            $user = $this->getUser();
            $communaute = $repository->findAll();
            if ($request->isXmlHttpRequest()) {
                $selectionComm = $request->get('selectionComm');
                $com = $repository->find($selectionComm);
            
                $membre = $repositoryMembre->findBy(['communaute' => $com->getId()]);
                $membres = [];
                foreach ($membre as $m) {
                   $membres[]= ['nom' => $m->getUser()->getNom(),'prenom' => $m->getUser()->getPrenom(),'adresse' => $m->getUser()->getAdresse(),'mail' => $m->getUser()->getEmail(),'dateInscrit' => $m->getDateDebut()->format('d/m/Y')];
                }
                return new JsonResponse(["message" => "success", 'data' => $membres]);
            }
            
            return $this->render('user/listeMembreParCommunaute.html.twig', [
                'communaute' => $communaute
            ]);
        }
    }

}
