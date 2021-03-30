<?php

namespace App\Controller;

use App\Entity\Post;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class AdminController
 * @package App\Controller
 * @Route("/admin")
 */
class AdminController extends AbstractController
{
    private $em;
    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    /**
     * @Route("/", name="admin")
     */
    public function index(): Response
    {
        return $this->render('admin/index.html.twig', [
            'controller_name' => 'AdminController',
        ]);
    }

    /**
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|Response
     * @Route("/admin/new-article.html", name="newArticleAdmin")
     */
    public function newArticle(Request $request){
        $post = new Post();
        $form = $this->createFormBuilder($post)
            ->add('contenus', TextareaType::class, ['required' => true])
            ->getForm();

        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()) {
            $infos = $request->get('form');
            if(isset($_FILES['image_encadre']) AND $_FILES['image_encadre']['error'] == 0){
                $encadreInfo = pathinfo($_FILES['image_encadre']['name']);
                $extensionencadre = $encadreInfo['extension'];
                $extensions_autorisees = array('jpg', 'jpeg', 'gif', 'png', 'PNG', 'JPG', 'JPEG', 'GIF');

                if(in_array($extensionencadre,$extensions_autorisees)) {
                    $nameFileEncdre = $encadreInfo['filename'].'.'.$extensionencadre;
                    move_uploaded_file($_FILES['image_encadre']['tmp_name'], 'imgEncadre/' . $nameFileEncdre);
                    $post->setUser($this->getUser())
                        ->setPublishedAt(new \DateTime())
                        ->setContenus($infos['contenus']);
                    $this->em->persist($post);
                    $this->em->flush();
                    return $this->redirectToRoute('admin');
                }
            }
        }
        return $this->render('admin/newPost.html.twig',[
            'form'=>$form->createView()
        ]);

    }
}
