<?php


namespace App\Controller\rest;


use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\View\View;
use Swift_Mailer;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoder;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class UserController extends FOSRestController
{
    private $em;

    /**
     * UserController constructor.
     * @param EntityManagerInterface $em
     */
    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    /**
     * @Rest\Post("/inscription")
     * @param Request $request
     * @param UserRepository $repository
     * @return View
     */
    public function register(Request $request, UserRepository $repository){
        $user = new User();
        $userverife = $repository->findUserByEmail($request->get('email'));
        if($userverife == null){
            if($request->get('pseudo') == null || $request->get('password') == null || $request->get('email') == null ){
                return View::create([
                    'status'=>0,
                    'message'=>'Les trois champs sont obligatoires',
                    'data'=>null

                ], Response::HTTP_OK);
            }else{
                $encoded = sha1($request->get('password'));
                $user->setPseudo($request->get('pseudo'))
                    ->setPassword($encoded)
                    ->setEmail($request->get('email'))
                    ->setModuleCompareSite(1)
                    ->setModuleSemantique(1)
                    ->setModuleSerpDor(1)
                    ->setStatut(1)
                    ->setRole(['ROLE_USER'])
                    ->setNom('')
                    ->setPrenom('')
                    ->setCivilite('');

                $this->em->persist($user);
                $this->em->flush();

                return View::create([
                    'status'=>1,
                    'message'=>'Inscription avec succès',
                    'data'=>$user
                ], Response::HTTP_CREATED);
            }
        }else{
            return View::create([
                'status'=>0,
                'message'=>'Un compte utilisateur est déjà associé à cette adresse email',
                'data'=>null
            ], Response::HTTP_OK);
        }

    }

    /**
     * @param Request $request
     * @param User $user
     * @return View
     * @Rest\Post("/complete/infosuser/{id}")
     */
    public function complete(Request $request, User $user){
        $user->setNom($request->get('nom'))
            ->setPrenom($request->get('prenom'))
            ->setCivilite($request->get('civilite'))
            ->setEmail($request->get('email'))
            ->setStatut(1);

        $this->em->persist($user);
        $this->em->flush();

        return View::create([
            'status'=>1,
            'message'=>'completion des informations avec succès',
            'data'=>$user
        ], Response::HTTP_CREATED);
    }

    /**
     * @param Request $request
     * @param UserRepository $repository
     * @Rest\Post("/login")
     * @return View
     */
    public function login(Request $request, UserRepository $repository){
        $pseudo = $request->get('pseudo');
        $password = sha1($request->get('password'));

        $user = $repository->findUser($pseudo, $password);

        if($user != null){
            return View::create(
                [
                    'status'=>1,
                    'message'=>'login avec succès',
                    'data'=>$user
                ], Response::HTTP_OK);
        }else{
            return View::create([
                'status'=>0,
                'message'=>'login echècs',
                'data'=>null
            ], Response::HTTP_OK);
        }
    }

    /**
     * @param Request $request
     * @param UserRepository $repository
     * @param Swift_Mailer $mailer
     * @return View
     * @Rest\Post("/pre/reset/password")
     */
    public function resetPassword(Request $request, UserRepository $repository, Swift_Mailer $mailer){
        $user = $repository->findUserByEmail($request->get('email'));

        if($user == null){
            return View::create(['error'=>'Cette adresse email n\'existe pas'], Response::HTTP_OK);
        }else{
            $message = (new \Swift_Message())
                ->setFrom('tribadd2020@gmail.com')
                ->setTo($request->get('email'))
                ->setBody('test');

            $mailer->send($message);
            return View::create($user, Response::HTTP_OK);
        }
    }

    /**
     * @param Request $request
     * @param User $user
     * @Rest\Post("/edit/password/{id}")
     * @return View
     */
    public function editPassword(Request $request, User $user){
        $passEncoded = sha1($request->get('password'));
        $user->setPassword($passEncoded);

        $this->em->persist($user);
        $this->em->flush();

        return View::create([
            'status'=>1,
            'message'=>'mt de passe modifié',
            'data'=>$user
        ], Response::HTTP_CREATED);
    }
}