<?php



namespace App\Controller;



use App\Entity\User;

use App\Entity\Membres;

use App\Entity\Communaute;

use App\Repository\PostRepository;

use App\Repository\UserRepository;

use App\Repository\RolesRepository;

use App\Repository\CommunauteRepository;

use Doctrine\ORM\EntityManagerInterface;

use Symfony\Component\HttpFoundation\Request;

use Symfony\Component\HttpFoundation\Response;

use Symfony\Component\Routing\Annotation\Route;

use Symfony\Component\HttpFoundation\JsonResponse;

use Symfony\Component\HttpFoundation\RedirectResponse;

use Symfony\Component\Form\Extension\Core\Type\DateType;

use Symfony\Component\Form\Extension\Core\Type\TextType;

use Symfony\Component\Form\Extension\Core\Type\PasswordType;

use Symfony\Component\Form\Extension\Core\Type\RepeatedType;

use Symfony\Component\Form\Extension\Core\Type\TextareaType;

use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;



class AuthentificationController extends AbstractController

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

     * @param AuthenticationUtils $utils

     * @return Response

     * @Route("/", name="login")

     */

    public function login(AuthenticationUtils $utils, CommunauteRepository $repository){
        
        $user = $this->getUser();
        
        if($user != null){

            return $this->redirectToRoute('user');

        }else{
            
            $communautes = $repository->findAll();
            
            $lasteUsername=$utils->getLastUsername();
            
            $error=$utils->getLastAuthenticationError();
            
            return $this->render('authentification/login.html.twig',[

                'lastUsername' => $lasteUsername,

                'error' => $error,

                'communaute' => $communautes

            ]);

        }

        /*$user = $this->em->getRepository(User::class)->find(1);

        $token = new UsernamePasswordToken($user,null,'frontoffice',$user->getRoles());

        $this->get('security.token_storage')->getToken()->setAuthenticated(false);

        $this->get('security.token_storage')->setToken($token);

        $this->get('security.token_storage')->getToken()->setUser($user);

        if ($this->getUser()) {

            return $this->redirectToRoute('user');

        }

        $communautes = $repository->findAll();

        $lasteUsername=$utils->getLastUsername();

        $error=$utils->getLastAuthenticationError();

        return $this->render('authentification/login.html.twig',[

            'lastUsername' => $lasteUsername,

            'error' => $error,

            'communaute' => $communautes

        ]);*/

    }

    /**
     * @param AuthenticationUtils $utils
     * 
     * @return Response

     * @Route("/login-{id}.html", name="loginPost")

     */

    public function showNonConnecte(AuthenticationUtils $utils, PostRepository $repository){

        $posts = $repository->findAll();

        $lasteUsername=$utils->getLastUsername();

        $error=$utils->getLastAuthenticationError();

        //dump($utils);

        return $this->render('authentification/login.html.twig',[

            'lastUsername'=>$lasteUsername,

            'error'=>$error,

            'post'=>$posts

        ]);

    }

    /**

     * @Route("/logout", name="logout")

     * @param Request $request

     * @return RedirectResponse

     */

    public function logout(Request $request){

        $request->getSession()->clear();
        $array = explode('/',$_GET['url']);
        
        if($array[1] == 'calendars'){
           
            $request->getSession()->clear();
            return $this->redirectToRoute('login');
        }
        $this->getUser()->setUsername(null)->setPassword(null)->setRoles([]);

        $request->getSession()->clear();

        //dd($array);

        return $this->redirect('/'.$array[2]);

    }



    /**

     * @Route("/deconnexion", name="deconnexion")

     * @param Request $request

     * @return RedirectResponse

     */

    public function deconnexion(Request $request){



    }



    /**

    * @param Request $request

    * @param Communaute $com

     * @return Response

     * @throws \Exception

     * @Route("/enregistrez-vous/ici/", name="registerUser")

     */

    public function register(Request $request, RolesRepository $rolesRepository,CommunauteRepository $cr){
       
        $communauteId = $request->get('id');

        $user = new User();

         $membres = new Membres();

        $form = $this->createFormBuilder($user)

            ->add('username', TextType::class, ['required' => true,

                'attr' => ['placeholder'=>'Pseudo']])

            ->add('password', RepeatedType::class, [

                'type' => PasswordType::class,

                'invalid_message' => 'Les mots de passe ne correspondent pas.',

                'options' => ['attr' => ['class' => 'form-control']],

                'required' => true,

                'first_options'  => ['attr' => ['placeholder' => 'Mot de passe']],

                'second_options' => ['attr' => ['placeholder' => 'Confirmer votre mot de passe']],

            ])

            ->add('nom', TextType::class, ['attr' => ['placeholder' => 'Nom'],'required' => false])

            ->add('prenom', TextType::class,['attr' => ['placeholder' => 'Prénom'],'required' => false])

            ->add('email', TextType::class, ['attr' => ['placeholder' => 'Votre email']])

            ->add('dateNaissance', DateType::class, ['widget' => 'single_text' ,'attr' => ['placeholder' => 'Votre date de naissance'], 'required' => false])

            ->getForm();

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){

            $infos = $request->get('form');

            $communauteId = $request->get('communaute');

            $devenir = $request->get('devenir');
            


            if(isset($_FILES['photo']) AND $_FILES['photo']['error'] == 0){

                $photo = pathinfo($_FILES['photo']['name']);

                $extensionphoto = $photo['extension'];

                $extensions_autorisees = array('jpg', 'jpeg', 'gif', 'png', 'PNG', 'JPG', 'JPEG', 'GIF');

                if(in_array($extensionphoto, $extensions_autorisees)) {

                    $nameFile = $photo['basename'] . '.' . $extensionphoto;

                    move_uploaded_file($_FILES['photo']['tmp_name'], 'photo_de_profil/' . $nameFile);

                    $user->setPhoto($nameFile);

                }

            }

            $mdp = sha1($infos['password']['first']);

            $user->setPrenom($infos['prenom'])

                ->setNom($infos['nom'])

                ->setEmail($infos['email'])

                ->setUsername($infos['username'])

                ->setPassword($mdp)

                ->setStatus(0)

                ->setDateNaissance(new \DateTime($infos['dateNaissance']))

                ->setRoles(['ROLE_USER'])

                ->setCreation(new \DateTime());

            $this->em->persist($user);

            $this->em->flush();
           
            if (isset($communauteId)) {
                 $communautes = $cr->find($communauteId);
                if ($devenir == true) {
                       $role = $rolesRepository->findOneBy(['communaute' => $communauteId, 'libelle' => 'membre']);
                      

                       $membres->setCommunaute($communautes)
                        ->setUser($user)
                        ->setRole($role)->setAbonneNewsLetter(true)
                        ->setDateDebut(new \DateTime());
                       $this->em->persist($membres);
                       $this->em->flush();
                 }

                    $token = new UsernamePasswordToken($user,null,'frontoffice',$user->getRoles());
                    $this->get('security.token_storage')->getToken()->setAuthenticated(false);
                    $this->get('security.token_storage')->setToken($token);
                    $this->get('security.token_storage')->getToken()->setUser($user);

                    return $this->redirect('/in/'.$communautes->getUrlPublic());

                 
              }
             

            return $this->redirectToRoute('sendRegister',['id'=>$user->getId()]);

        }

        return $this->render('user/register.html.twig',['form'=>$form->createView(),'communaute'=> $communauteId]);

    }



    /**

     * @param User $user

     * @return RedirectResponse

     * @Route("/create/token-{id}.html", name="createToken")

     */

    public function createTokenForUserAction(User $user){

        $token = new UsernamePasswordToken($user,null,'frontoffice',$user->getRoles());

        $this->get('security.token_storage')->getToken()->setAuthenticated(false);

        $this->get('security.token_storage')->setToken($token);

        $this->get('security.token_storage')->getToken()->setUser($user);

        return $this->redirectToRoute('home');

    }

    /**

     * @param \Swift_Mailer $mailer

     * @param User $user

     * @return Response

     * @Route("/send/register-{id}.html", name="sendRegister")

     */

    public function sendEmailRegister(\Swift_Mailer $mailer, User $user){

        $message = (new \Swift_Message('Confirmation de votre compte'))

            ->setFrom('tribadd2021.jmc@gmail.com')

            ->setTo($user->getEmail())

            ->setBody(

                $this->renderView(

                    'emails/registration.html.twig'),

                'text/html'

            );

        $mailer->send($message);

        return $this->redirectToRoute('createToken',['id'=>$user->getId()]);

    }

    /**

     * @Route("/mot-de-passe/oublie.html", name="passForgot")

     * @param Request $request

     * @param \Swift_Mailer $mailer

     * @param UserRepository $userRepository

     * @return Response

     */

    public function passwordOublie(Request $request, \Swift_Mailer $mailer, UserRepository $userRepository){

        if(isset($_POST['email'])){

            $user = $userRepository->findByEmail($_POST['email']);

            if($user != null){

                $this->addFlash('success','Une email est envoyée sur l\'adresse '.$_POST['email']);

                // dump($user[0]->getId());die();

                $url = $this->generateUrl('password', ['id'=>$user[0]->getId()], UrlGeneratorInterface::ABSOLUTE_URL);

                $message = (new \Swift_Message('Reinitialisation de mot de passe'))

                    ->setFrom('tribadd2021.jmc@gmail.com')

                    ->setTo($_POST['email'])

                    ->setBody(

                        $this->renderView(

                            'emails/passoublie.html.twig',['url'=>$url]),

                        'text/html'

                    );

                $mailer->send($message);

                // return $this->redirectToRoute('passForgot');

            }else{

                return $this->render('authentification/passForgot.html.twig',['error'=>'Cette adresse email n\'existe pas.']);

            }



        }

        return $this->render('authentification/passForgot.html.twig',['error'=>null]);

    }



     /**

     * @param AuthenticationUtils $utils

     * @return Response

     * @Route("/condition-general.html", name="cguonnecte")

     */

    public function cgu(AuthenticationUtils $utils){

        $user = $this->getUser();

        if($user != null){

            return $this->redirectToRoute('cgunonConnecte');

        }else{

            $lasteUsername=$utils->getLastUsername();

            $error=$utils->getLastAuthenticationError();

            return $this->render('user/cgu.html.twig',[

                'lastUsername' => $lasteUsername,

                'error' => $error,

            ]);

        }

    }





}

