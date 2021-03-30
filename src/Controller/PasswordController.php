<?php

namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class PasswordController extends AbstractController
{
    private $em;
    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }
    /**
     * @Route("/reinitialisation/mot-de-passe-{id}.html", name="password")
     * @param User $user
     * @param Request $request
     * @param UserPasswordEncoderInterface $encoder
     * @return ResponseAlias
     */
    public function index(User $user, Request $request, UserPasswordEncoderInterface $encoder)
    {
        $form = $this->createFormBuilder($user)
            ->add('password', RepeatedType::class,[
                'type' => PasswordType::class,
                'invalid_message' => 'Les champs du mot de passe doivent identique.',
                'options' => ['attr' => ['class' => 'password-field']],
                'required' => true,
                'first_options'  => ['attr' => ['placeholder' => 'Mot de passe']],
                'second_options' => ['attr' => ['placeholder' => 'Confirmer votre mot de passe']],
            ])
            ->getForm();
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()){
            $info = $request->get('form');
            $mdp = $encoder->encodePassword($user,$info['password']['first']);
            $user->setPassword($mdp);
            $this->em->persist($user);
            $this->em->flush();
            return $this->redirectToRoute('login');
        }
        return $this->render('password/index.html.twig',[
            'form'=>$form->createView()
        ]);
    }
}
