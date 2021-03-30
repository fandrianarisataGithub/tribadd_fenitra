<?php

namespace App\Controller;


use App\Entity\Post;
use App\Entity\User;
use App\Entity\Forum;
use App\Entity\Video;
use App\Entity\Article;
use App\Entity\Membres;
use App\Entity\Message;
use App\Entity\Communaute;
use App\Entity\Commentaires;
use App\Repository\PostRepository;
use App\Repository\UserRepository;
use App\Repository\VideoRepository;
use App\Repository\ArticleRepository;
use App\Repository\MembresRepository;
use App\Repository\CommunauteRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;


class DefaultController extends AbstractController
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
     * @Route("/", name="home")
     * @return RedirectResponse
     */
    public function index()
    {
        if($this->getUser() == null) {
            return $this->redirectToRoute('login');
        } else {
            return $this->redirectToRoute('user');
        }
    }
    /**
     * @return Response
     * @Route("/forbiden")
     */
    public function forbiden(){
        return $this->render('default/forbiden.html.twig');
    }

    /**
     * @param Communaute $communaute
     * @param Request $request
     * @param MembresRepository $repository
     * @param EntityManagerInterface $manager
     * @return Response
     * @Route("/{urlPublic}", name="ShowmyCommunaute1")
     */
    public function ShowmyCommunaute(Communaute $communaute, Request $request, MembresRepository $repository, EntityManagerInterface $manager, UserRepository $userRepository)
    {
        if($this->getUser() != null){
           return $this->redirect('/in/'.$communaute->getUrlPublic());
        }
        if(isset($_POST['login']) and $_POST['login'] == "Envoyer")
        {
            $username = $_POST['_username'];
            $password = $_POST['_password'];
            $path = $_POST['path'];
            // dd($path);
            $user = $userRepository->findUser($username, sha1($password));
            if($user != null){
                $token = new UsernamePasswordToken($user,null,'frontoffice',$user->getRoles());
                $this->get('security.token_storage')->getToken()->setAuthenticated(false);
                $this->get('security.token_storage')->setToken($token);
                $this->get('security.token_storage')->getToken()->setUser($user);
                return $this->redirect('/in'.$path);
            }else{
                return $this->render('user/showCommunaute.html.twig', [
                    'communaute' => $communaute,
                    'error'=>"L'authentification n'est pas validé",
                    'lastUsername'=>$username
                ]);
            }
        }

        if ($this->getUser() != null) {
            return $this->redirectToRoute('ShowmyCommunaute', ['id' => $communaute->getId()]);
        }
        return $this->render('user/showCommunaute.html.twig', ['communaute' => $communaute]);
    }

    /**
     * @Route("/{urlPublic}/post", name="showPost")
     * @param Communaute $communaute
     * @param Request $request
     * @param MembresRepository $repository
     * @param PostRepository $postRepository
     * @return Response
     */
    public function showPostCommunaute(Communaute $communaute, Request $request, MembresRepository $repository, PostRepository $postRepository){
        $post=$postRepository->find($_GET['id']);
        $user = $this->getUser();
        if ($this->getUser() != null) {
            return $this->redirectToRoute('showPostCommunaute', ['id' => $post->getId()]);
        }
        return $this->render('user/showPostCommunaute.html.twig', [
            'post' => $post,
            'communaute' => $post->getCommaunaute()
        ]);
    }

    /**
     * @Route("/{urlPublic}/contact", name = "contactUs")
     * @param Request $request
     * @param Communaute $communaute
     * @param \Swift_Mailer $mailer
     * @param EntityManagerInterface $manager
     * @param UserRepository $userRepository
     * @return Response
     */
    public function contactUs(Request $request, Communaute $communaute, \Swift_Mailer $mailer, EntityManagerInterface $manager, UserRepository  $userRepository) {
        if($this->getUser() != null){
            return $this->redirect('/in/'.$communaute->getUrlPublic());
        }
        if ($this->getUser() != null)
            return $this->redirectToRoute('contactNous', ['id' => $communaute->getId()]);

        $fondateur = $communaute->getUser();
        $message = new Message();
        $form = $this->createFormBuilder($message)
            ->add('contenu', TextareaType::class, ['attr' => ['placeholder' => 'Tapez votre message ici..']])
            ->getForm();
        $form->handleRequest($request);
        if(isset($_POST['login']) and $_POST['login'] == "Envoyer")
        {
            $username = $_POST['_username'];
            $password = $_POST['_password'];
            $path = $_POST['path'];
            $user = $userRepository->findUser($username, sha1($password));
            if($user != null) {
                $token = new UsernamePasswordToken($user, null, 'frontoffice', $user->getRoles());
                $this->get('security.token_storage')->getToken()->setAuthenticated(false);
                $this->get('security.token_storage')->setToken($token);
                $this->get('security.token_storage')->getToken()->setUser($user);
                return $this->redirect("/in".$path);
            }else{
                return $this->render('user/message.html.twig', [
                    'communaute' => $communaute,
                    'form' => $form->createView(),
                    'error' => "L'authentification n'est pas validé",
                    'lastUsername' => $username
                ]);
            }
        }
        if($form->isSubmitted() && $form->isValid()){
            $this->addFlash('success', 'Votre message est bien envoyé!');
            $infos = $request->get('form');
            $message->setContenu($infos['contenu']);
                // ->setUser($request->request->get('username'));
            $manager->persist($message);
            $manager->flush();
            $email = (new \Swift_Message('Objet'))
                ->setFrom(trim($request->request->get('email')))
                ->setTo(trim($fondateur->getEmail()))
                ->setBody(
                    $this->renderView('emails/contact.html.twig',[
                        'message'=>$infos['contenu']
                    ]),
                    'text/html'
                );
            $mailer->send($email);

            // return $this->redirectToRoute('contactUs', ['id'=>$communaute->getId()]);

        }
        return $this->render('user/message.html.twig',[
            'form' => $form->createView(),
            'communaute' => $communaute
        ]);
    }

    /**
     * @Route("/contact/nous", name = "contactNousAccueilNotUser")
     * @param Request $request
     * @param \Swift_Mailer $mailer
     * @param UserRepository $userRepository
     * @return Response
     */
    public function contactNousAccueilNotUser(Request $request, \Swift_Mailer $mailer, UserRepository  $userRepository){
        $user = $this->getUser();
        if ($user != null)
            return $this->redirectToRoute('contactNousAccueil');
        $message = new Message();
        $form = $this->createFormBuilder($message)->getForm();
        $form->handleRequest($request);
        if(isset($_POST['login']) and $_POST['login'] == "Envoyer")
        {
            $username = $_POST['_username'];
            $password = $_POST['_password'];
            $path = $_POST['path'];
            $user = $userRepository->findUser($username, sha1($password));
            if($user != null){
                $token = new UsernamePasswordToken($user,null,'frontoffice',$user->getRoles());
                $this->get('security.token_storage')->getToken()->setAuthenticated(false);
                $this->get('security.token_storage')->setToken($token);
                $this->get('security.token_storage')->getToken()->setUser($user);
                return $this->redirect($path);
            } else {
                return $this->render('user/message2.html.twig', [
                    'form' => $form->createView(),
                    'error' => "L'authentification n'est pas validé",
                    'lastUsername' => $username
                ]);
            }
        }
        if($form->isSubmitted() && $form->isValid()){
            $this->addFlash('success', 'Votre message est bien envoyé!');
            $infos = $request->get('form');
            $message->setContenu($_POST['contenu'])
                ->setUser($user)
                ->setNameSender(isset($_POST['username'])?$_POST['username']:'');
            $this->em->persist($message);
            $this->em->flush();
            $email = (new \Swift_Message('Message TribADD : '.$_POST['objet']))
                ->setFrom($_POST['email'])
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
     * @Route("/{urlPublic}/presentation", name="showPresentation")
     * @param Communaute $communaute
     * @param Request $request
     * @return Response
     */
    public function showPrestation(Communaute $communaute, Request $request, UserRepository $userRepository) {
        if($this->getUser() != null){
            return $this->redirect('/in/'.$communaute->getUrlPublic());
        }
        if(isset($_POST['login']) and $_POST['login'] == "Envoyer")
        {
            $username = $_POST['_username'];
            $password = $_POST['_password'];
            $path = $_POST['path'];
            $user = $userRepository->findUser($username, sha1($password));
            if($user != null) {
                $token = new UsernamePasswordToken($user, null, 'frontoffice', $user->getRoles());
                $this->get('security.token_storage')->getToken()->setAuthenticated(false);
                $this->get('security.token_storage')->setToken($token);
                $this->get('security.token_storage')->getToken()->setUser($user);
                return $this->redirect($path);
            }else{
                return $this->render('presentation.html.twig', [
                    'communaute' => $communaute,
                    'error'=>"L'authentification n'est pas validé",
                    'lastUsername'=>$username
                ]);
            }
        }
        if ($this->getUser() != null)
            return $this->redirectToRoute('UsershowPresentation');

        return $this->render('presentation.html.twig', [
            'communaute' => $communaute,
        ]);

    }

    /**
     * @Route("/{urlPublic}/articles", name="showArticles")
     * @param Communaute $communaute
     * @param Request $request
     * @return Response
     */
    public function getArticles(Communaute $communaute, Request $request, UserRepository $userRepository) {
        if($this->getUser() != null){
            return $this->redirect('/in/'.$communaute->getUrlPublic().'/articles');
        }
        if(isset($_POST['login']) and $_POST['login'] == "Envoyer")
        {
            $username = $_POST['_username'];
            $password = $_POST['_password'];
            $path = $_POST['path'];
            $user = $userRepository->findUser($username, sha1($password));
            if($user != null) {
                $token = new UsernamePasswordToken($user, null, 'frontoffice', $user->getRoles());
                $this->get('security.token_storage')->getToken()->setAuthenticated(false);
                $this->get('security.token_storage')->setToken($token);
                $this->get('security.token_storage')->getToken()->setUser($user);
                return $this->redirect('/in'.$path);
            }else{
                return $this->render('user/showArticleCommunaute.html.twig', [
                    'communaute' => $communaute,
                    'error'=>"L'authentification n'est pas validé",
                    'lastUsername'=>$username
                ]);
            }
        }
        if ($this->getUser() != null) {
            return $this->redirectToRoute('userShowArticles', ['id' => $communaute->getId()]);
        }
        return $this->render('user/showArticleCommunaute.html.twig', [
            'communaute' => $communaute
        ]);
    }

    /**
     * @Route("/{urlPublic}/videos", name="listVideo")
     * @param Communaute $communaute
     * @return Response
     */
    public function indexVideo(Communaute $communaute, UserRepository $userRepository){
        if($this->getUser() != null){
            return $this->redirect('/in/'.$communaute->getUrlPublic().'/videos');
        }
        if(isset($_POST['login']) and $_POST['login'] == "Envoyer")
        {
            $username = $_POST['_username'];
            $password = $_POST['_password'];
            $path = $_POST['path'];
            // dd($path);
            $user = $userRepository->findUser($username, sha1($password));
            if($user != null) {
                $token = new UsernamePasswordToken($user, null, 'frontoffice', $user->getRoles());
                $this->get('security.token_storage')->getToken()->setAuthenticated(false);
                $this->get('security.token_storage')->setToken($token);
                $this->get('security.token_storage')->getToken()->setUser($user);
                return $this->redirect('/in'.$path);
            }else{
                return $this->render('user/video.html.twig', [
                    'communaute' => $communaute,
                    'error'=>"L'authentification n'est pas validé",
                    'lastUsername'=>$username
                ]);
            }
        }
        if ($this->getUser() != null) {
            return $this->redirectToRoute('indexVideo', ['id' => $communaute->getId()]);
        }
        return $this->render('user/video.html.twig',[
            'communaute'=>$communaute
        ]);
    }

    /**
     * @Route("/{urlPublic}/video", name="voirVideo")
     * @param Video $video
     * @param VideoRepository $repository
     * @return Response
     */
    public function showVideo(Communaute $communaute, VideoRepository $videoRepository, VideoRepository $repository){
        $video = $videoRepository->find($_GET['id']);
        $communaute = $video->getCommunaute();

        $Videoss = $repository->findVideo($communaute, $video->getFichier());

        if ($this->getUser() != null) {
            return $this->redirectToRoute('viewVideo', ['urlPublic'=>$communaute->getUrlPublic(), 'id' => $video->getId()]);
        }

        return $this->render('user/showVideo.html.twig',[
            'video'=>$video,
            'communaute'=>$communaute,
            'others'=>$Videoss
        ]);
    }

    /**
     * @Route("/recherche/communaute", name="Defaultrecherce")
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
     * @Route("/{urlPublic}/article", name="showArticle")
     * @param Article $article
     * @param Request $request
     * @param UserRepository $userRepository
     * @return Response
     */
    public function showArticle(Communaute $communaute, ArticleRepository $repository, Request $request, UserRepository $userRepository) {
        $article = $repository->find($_GET['id']);
        $user2 = $this->getUser();
        if($user2 != null){
            return $this->redirect('/in/'.$communaute->getUrlPublic().'/article?id='.$_GET['id']);
        }
        if(isset($_POST['login']) and $_POST['login'] == "Envoyer")
        {
            $username = $_POST['_username'];
            $password = $_POST['_password'];
            $path = $_POST['path'];
            $user = $userRepository->findUser($username, sha1($password));
            if($user != null) {
                $token = new UsernamePasswordToken($user, null, 'frontoffice', $user->getRoles());
                $this->get('security.token_storage')->getToken()->setAuthenticated(false);
                $this->get('security.token_storage')->setToken($token);
                $this->get('security.token_storage')->getToken()->setUser($user);
                return $this->redirect($path);
            }else{
                return $this->render('user/showArticle.html.twig', [
                    'communaute' => $article->getCommunaute(),
                    'article' => $article,
                    'error' => "L'authentification n'est pas validé",
                    'lastUsername' => $username
                ]);
            }
        }
        if ($this->getUser() != null) {
            return $this->redirectToRoute('userShowArticle', ['id' => $article->getId()]);
        }

        return $this->render('user/showArticle.html.twig', [
            'article' => $article,
            'communaute' => $article->getCommunaute()
        ]);
    }

    /**
     * @Route("/myPhotos-{id}.html", name="myPhotos")
     * @param Communaute $communaute
     * @param Request $request
     * @return Response
     */
    public function myPhotos(Communaute $communaute, Request $request) {
        return $this->render('user/myPhotos.html.twig', [
            'communaute' => $communaute
        ]);
    }


    /**
     * @Route("/{urlPublic}/forum", name="defaultforum")
     * @param Communaute $communaute
     * @param Request $request
     * @param UserRepository $userRepository
     * @param MembresRepository $repositoryMembre
     * @return Response
     * @throws \Exception
     */
    public function forum(Communaute $communaute, Request $request, UserRepository $userRepository,MembresRepository $repositoryMembre) {
        $forum= new Forum();
        $forums = $this->em->getRepository(Forum::class)->findBy(['communaute' => $communaute]);
        $user = $this->getUser();
        if($user != null){
           return $this->redirect('/in/'.$communaute->getUrlPublic().'/forum');
        }
        $form = $this->createFormBuilder($forum)
            ->add('titre', TextType::class)
            ->add('contenu', TextareaType::class)
        ->getForm();
        $form->handleRequest($request);
        if(isset($_POST['login']) and $_POST['login'] == "Envoyer")
        {
            $username = $_POST['_username'];
            $password = $_POST['_password'];
            $path = $_POST['path'];
            $user = $userRepository->findUser($username, sha1($password));
            if($user != null){
                $token = new UsernamePasswordToken($user,null,'frontoffice',$user->getRoles());
                $this->get('security.token_storage')->getToken()->setAuthenticated(false);
                $this->get('security.token_storage')->setToken($token);
                $this->get('security.token_storage')->getToken()->setUser($user);
                return $this->redirect('/in'.$path);
            }else{
                return $this->render('user/gestionForum.html.twig', [
                    'communaute' => $communaute,
                    'forums' => $forums,
                    'form' => $form->createView(),
                    'error' => "L'authentification n'est pas validé",
                    'lastUsername' => $username
                ]);
            }
        }
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
            'forums' => $forums,
            'form' => $form->createView()
        ]);

    }

   /**
     * @Route("/{urlPublic}/condition-general", name="cguConnecte")
     * @param Communaute $communaute
     * @param Request $request
     * @param UserRepository $userRepository
     * @param MembresRepository $repositoryMembre
     * @return Response
     * @throws \Exception
     */
    /*public function cgu(Communaute $communaute, Request $request, UserRepository $userRepository,MembresRepository $repositoryMembre) {
       
        $user = $this->getUser();
        $membre = $repositoryMembre->findOneBy(['user' => $user, 'communaute' => $communaute]);
      
        return $this->render('user/cguCommunaute.html.twig', [
            'communaute' => $communaute,
            'membre' => $membre
        ]);
    }
*/

    /**
     * @Route("/condition/generale", name = "cguAccueilNotUser")
     * @param Request $request
     * @param UserRepository $userRepository
     * @return Response
     */
    public function cguAccueilNotUser(Request $request, UserRepository  $userRepository){
        $user = $this->getUser();
        if ($user != null)
            return $this->redirectToRoute('cguAccueil');
       
        if(isset($_POST['login']) and $_POST['login'] == "Envoyer")
        {
            $username = $_POST['_username'];
            $password = $_POST['_password'];
            $path = $_POST['path'];
            $user = $userRepository->findUser($username, sha1($password));
            if($user != null){
                $token = new UsernamePasswordToken($user,null,'frontoffice',$user->getRoles());
                $this->get('security.token_storage')->getToken()->setAuthenticated(false);
                $this->get('security.token_storage')->setToken($token);
                $this->get('security.token_storage')->getToken()->setUser($user);
                return $this->redirect($path);
            } else {
                return $this->render('user/cgu.html.twig', [
                    'error' => "L'authentification n'est pas validé",
                    'lastUsername' => $username
                ]);
            }
        }
        
        return $this->render('user/cgu.html.twig');
    }

    /**
     * @Route("/{urlPublic}/condition/generale", name="cguDefault")
     * @param Communaute $communaute
     * @param Request $request
     * @param UserRepository $userRepository
     * @param MembresRepository $repositoryMembre
     * @return Response
     * @throws \Exception
     */
    public function cguCommunaute(Communaute $communaute, Request $request, UserRepository $userRepository,MembresRepository $repositoryMembre) {
        $user = $this->getUser();
        if($user != null){
           return $this->redirect('/in/'.$communaute->getUrlPublic().'/condition/generale');
        }
        if(isset($_POST['login']) and $_POST['login'] == "Envoyer")
        {
            $username = $_POST['_username'];
            $password = $_POST['_password'];
            $path = $_POST['path'];
            $user = $userRepository->findUser($username, sha1($password));
            if($user != null){
                $token = new UsernamePasswordToken($user,null,'frontoffice',$user->getRoles());
                $this->get('security.token_storage')->getToken()->setAuthenticated(false);
                $this->get('security.token_storage')->setToken($token);
                $this->get('security.token_storage')->getToken()->setUser($user);
                return $this->redirect('/in'.$path);
            }else{
                return $this->render('user/cguCommunaute.html.twig', [
                    'communaute' => $communaute,
                    //'form' => $form->createView(),
                    'error' => "L'authentification n'est pas validé",
                    'lastUsername' => $username
                ]);
            }
        }
        return $this->render('user/cguCommunaute.html.twig', [
            'communaute' => $communaute,
        ]);

    }
    
}