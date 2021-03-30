<?php

namespace App\Controller;

use DateTime;
use App\Kernel;
use App\Entity\Event;
use App\Entity\Message;
use App\Entity\Communaute;
use App\Entity\PersonParticipant;
use App\Repository\EventRepository;
use App\Repository\MembresRepository;
use App\Repository\CommunauteRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Bundle\MakerBundle\EventRegistry;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;
use App\Repository\PersonParticipantRepository;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ColorType;
use Doctrine\Common\Persistence\Mapping\Driver\FileLocator;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpKernel\Config\FileLocator as ConfigFileLocator;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class CalendarController extends AbstractController
{
    private $em;
    protected $parameterBag;

    public function __construct(EntityManagerInterface $em, ParameterBagInterface $parameterBag)
    {
        $this->em = $em;
        $this->parameterBag = $parameterBag;
    }

    /**
     * @Route("/calendars/{id}", name="showCalendar")
     * @param Request $request
     * @param MembresRepository $repositoryMembre
     * @param Communaute $repoCom
     * @return Response
     */
    public function showCalendar($id,CommunauteRepository $repoCom, Request $request, MembresRepository $repositoryMembre)
    {   
        
        $currentWeekNumber = date('W');
        //dd('Week number:' . $currentWeekNumber);

        $communaute = $repoCom->find($id);
        $user = $this->getUser();
        
        $events = $this->em->getRepository(Event::class)->findBy(['communaute' => $communaute]);
        $membre = $repositoryMembre->findOneBy(['user' => $user, 'communaute' => $communaute]);

        $listes = [];
        $event_in_this_week = 0;
        $mode_affichage = 'dayGridMonth';
        foreach($events as $item){
            $his_week = $item->getCreatedAt()->format('W');
            if($his_week == $currentWeekNumber){
                $event_in_this_week++;
            }
            //dd($his_week);
            $listes[] = [
                'id'                =>$item->getId(),
                'start'             =>$item->getDateDebut()->format('Y-m-d H:i'),
                'end'               =>$item->getDateFin()->format('Y-m-d H:i'),
                'title'             =>$item->getTitle(),
                'description'       =>$item->getDescription(),
                'backgroundColor'   =>$item->getCouleurFond(),
                'lieu'              =>$item->getLieu(),   
                'participant'       =>$item->getParticipant(),
                'image'             => $item->getImage(),
            ];
        }
        if($event_in_this_week > 2){
            $mode_affichage = 'listWeek';
        }
        $data = json_encode($listes);
        return $this->render('calendar/show_calendar.html.twig', [
            'communaute'            => $communaute,
            'clicked'               => true,
            'data'                  => $data,
            'membre'                => $membre,
            'user'                  => $user,
            'calendar'              => true,
            'mode_affichage'        => $mode_affichage,
        ]);
            
    }

    /**
     * @Route("/calendars/nouvel-event/{communaute_id}", name="newEvent")
     * @Route("/calendars/event/editer/{id}/{communaute_id}", name="editEvent")
     * @return Response
     * @param Request $request
     * @param MembresRepository $membresRepository
     * @param CommunauteRepository $repoCom
     * @param EventRepository $repoEvent
     * 
    */
    public function addNewEvent($id = null, $communaute_id, Request $request, 
        MembresRepository $membresRepository, CommunauteRepository $repoCom, EventRepository $repoEvent) {
        $communaute = $repoCom->find($communaute_id);   
        $event = null;
        $modif = true;
        if($id){
            $event = $repoEvent->find($id);
            $communaute = $event->getCommunaute();
        }  

        if(!$event){
            $modif = false;
            $event = new Event();
        }  
        
        $user = $this->getUser();
        $membre = $membresRepository->findOneBy(['user' => $user, 'communaute' => $communaute]);
        
        $form = $this->createFormBuilder($event)
            ->add('title', TextType::class, [
                "required" => true,
                'attr' => ["placeholder" => "titre"]
            ])
            ->add('description', TextareaType::class, [
                "required" => true,
            ])
            ->add('lieu', TextType::class, [
                "required" => true,
                "attr" => ["placeholder" => "Lieu..."]
            ])
            ->add('date_debut', DateType::class, [
                "required" => true,
                //"date_widget" => 'single_text', //raha datetimetype
                'widget' => 'single_text',
                
            ])
            ->add('date_fin', DateType::class, [
                "required" => true,
                //"date_widget" => 'single_text',
                'widget' => 'single_text',
                
            ])
            ->add('couleur_fond', ColorType::class, [
                "required" => true,
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
                        while (file_exists('events/' . $nameFile . '.' . $extension)) {
                            $nameFile = $nameFile . '-copy';
                        }
                        move_uploaded_file($_FILES['image']['tmp_name'], 'events/' . $nameFile . '.' . $extension);
                        $event->setImage($nameFile . '.' . $extension);
                    }
                }
                //dd($infos);
                $date_debut = $form->getData()->getDateDebut();
                $date_fin = $form->getData()->getDateFin();
                $duree = date_diff($date_fin, $date_debut);
                $annee = $duree->format('%y');
                $mois = $duree->format('%m');
                $jour = $duree->format('%d');
                $heure = $duree->format('%h');
                $min = $duree->format('%i');
                $duree_text = "";
                if($annee != "0"){
                    if($mois != "0"){
                        if($jour != "0"){
                            if($heure != "0"){
                                if($min != "0"){
                                    $duree_text = $annee . "an " . $mois . "mois " . $jour . "jour " . $heure . "h" . $min . "min";
                                }else{
                                    $duree_text = $annee . "an " . $mois . "mois " . $jour . "jour " . $heure . "h";
                                }
                            }else{
                                if($min != "0"){
                                    $duree_text = $annee . "an " . $mois . "mois " . $jour . "jour " . $min . "min";
                                }else{
                                    $duree_text = $annee . "an " . $mois . "mois " . $jour . "jour ";
                                }
                            }
                        }else{
                            if($heure != "0"){
                                if($min != "0"){
                                    $duree_text = $annee . "an " . $mois . "mois " . $heure . "h" . $min . "min";
                                }else{
                                    $duree_text = $annee . "an " . $mois . "mois " . $heure . "h" ;
                                }
                            }else{
                                if($min != "0"){
                                    $duree_text = $annee . "an " . $mois . "mois " . $min . "min";
                                }else{
                                    $duree_text = $annee . "an " . $mois . "mois ";
                                }
                            }
                        }
                    }else{
                        if($jour != "0"){
                            if($heure != "0"){
                                if($min != "0"){
                                    $duree_text = $jour . "jour " . $heure . "h" . $min . "min";
                                }else{
                                    $duree_text = $jour . "jour " . $heure . "h";
                                }
                            }else{
                                if($min != "0"){
                                    $duree_text = $jour . "jour " . $min . "min";
                                }else{
                                    $duree_text = $jour . "jour ";
                                }
                            }
                        }else{
                            if($heure != "0"){
                                if($min != "0"){
                                    $duree_text = $heure . "h" . $min . "min";
                                }else{
                                    $duree_text = $heure . "h";
                                }
                            }else{
                                if($min != "0"){
                                    $duree_text = $min . "min";
                                }else{
                                    $duree_text = $min . "0";
                                }
                            }
                        }
                    }
                }
                else if($annee == "0"){
                    if($mois != "0"){
                        if($jour != "0"){
                            if($heure != "0"){
                                if($min != "0"){
                                    $duree_text = $mois . "mois " . $jour . "jour " . $heure . "h" . $min . "min";
                                }else{
                                    $duree_text = $mois . "mois " . $jour . "jour " . $heure . "h";
                                }
                            }else{
                                if($min != "0"){
                                    $duree_text = $mois . "mois " . $jour . "jour " . $min . "min";
                                }else{
                                    $duree_text = $mois . "mois " . $jour . "jour " ;
                                }
                            }
                        }else{
                            if($heure !="0"){
                                if($min != "0"){
                                    $mois . "mois " . $heure . "h" . $min . "min";
                                }else{
                                    $duree_text = $mois . "mois " . $heure . "h";
                                }
                            }else{
                                if($min !="0"){
                                    $duree_text = $mois . "mois " . $min . "min";
                                }else{
                                    $duree_text = $mois . "mois ";
                                }
                            }
                        }
                    }else{
                        if($jour !="0"){
                            if($heure != "0"){
                                if($min != "0"){
                                    $duree_text = $jour . "jour " . $heure . "h" . $min . "min";
                                }else{
                                    $duree_text = $jour . "jour " . $heure . "h";
                                }
                            }else{
                                if($min != "0"){
                                    $duree_text = $jour . "jour " . $min . "min";
                                }else{
                                    $duree_text = $jour . "jour ";
                                }
                            }
                        }else{
                            if($heure != "0"){
                                if($min != "0"){
                                    $duree_text = $heure . "h" . $min . "min";
                                }else{
                                    $duree_text = $heure . "h";
                                }
                            }else{
                                if($min !="0"){
                                    $duree_text = $min . "min";
                                }
                                else{
                                    $duree_text = "0";
                                }
                            }
                        }
                    }
                }
                //dd($duree_text);
                //$duree_text = $duree_text = $annee . "an " . $mois . "mois " . $jour . "jour " . $heure . "h" . $min . "min";
                $event->setDuree($duree_text);
                $event->setCommunaute($communaute);
                // create participan
                $event->setUser($user);
                $event->setCreatedAt(new \DateTime());
                $event->setCreateur($user->getNom());
                $this->em->persist($event);
                $personPar = new PersonParticipant();
                $personPar->setNom($user->getNom());
                $personPar->setPrenom($user->getPrenom());
                $personPar->setEmail($user->getEmail());
                $this->em->persist($personPar);
                $event->addPersonParticipant($personPar);
                $this->em->flush();
                
                return $this->redirectToRoute('voir_detail_event', ['id' => $event->getId()]);
            }
        }
        return $this->render('calendar/addNewEvent.html.twig', [
            'communaute'        => $communaute,
            'form'              => $form->createView(),
            'membre'            => $membre,
            'user'              => $user,
            'calendar'          => true,
            'event'             => $event,
            'modif'             => $modif,
        ]);
            
    }
    /**
     * @Route("/calendars/voir_detail_event/{id}", name="voir_detail_event")
     */
    public function voir_detail_event(Event $event, Request $request, MembresRepository $membresRepository) :Response
    {
        $communaute = $event->getCommunaute();
        $user = $this->getUser();
        // if($user == null){
        //     return $this->redirect('/');
        // }
        $membre = $membresRepository->findOneBy(['user' => $user, 'communaute' => $event->getCommunaute()]);
        return $this->render("user/showDetailEvent.twig", [
            'event' => $event,
            'communaute' => $event->getCommunaute(),
            'membre' => $membre
        ]);
    }

    /**
     * @Route("/calendars/remove_event/{id}", name="remove_event")
     */
    public function remove_event(KernelInterface $kernel, Event $event, Request $request, MembresRepository $membresRepository, EntityManagerInterface $manager) :Response
    {   
        $communaute = $event->getCommunaute();
        $project_dir = $kernel->getProjectDir();
        $fs = new Filesystem();  
        $manager->remove($event);
        $manager->flush();    
        $fs->remove($project_dir .'/public/events/1.jpg');

        // redirection vers calendars
        return $this->redirectToRoute('showCalendar', ['id' => $communaute->getid()]);
    }

    /**
     * @Route("/calendars/event/participate", name = "add_participant")
     */
    public function add_participant(Request $request, EventRepository $repoEvent, 
    EntityManagerInterface $manager, PersonParticipantRepository $repoPart)
    {
        $response = new Response();
        if($request->isXmlHttpRequest()){
            $event = $repoEvent->find($request->get('event_id'));
            $data = "error";
            $nom = mb_strtoupper($request->get('nom'), "UTF-8");
            $prenom = $request->get('prenom');
            $email = $request->get('email');
            $tab_mail = explode("@", $email);
            
            if(count($tab_mail)>= 2){
                // eto le tsy mety
               $p = $event->getPersonParticipants();
               $p_exist = false;
               foreach($p as $item_p){
                   $son_email = $item_p->getEmail();
                   if($son_email == $email){
                       $p_exist = true;
                       break;
                   }
               }
                
                if($p_exist == false){
                    $persParticipant = new PersonParticipant();
                    $persParticipant->addEvent($event);
                    $persParticipant->setNom($nom);
                    $persParticipant->setEmail($email);
                    $persParticipant->setPrenom($prenom);
                    $manager->persist($persParticipant);
                    $manager->flush();
                    $nb_participant = count($event->getPersonParticipants());
                    $event->setParticipant($nb_participant);
                    $manager->flush();
                    $data = json_encode("ok");
                }else{
                    $data = json_encode("error:exist");
                }

            }else{
                $data = json_encode("error mail");
            }
            
            $response->headers->set('Content-Type', 'application/json');
            $response->setContent($data);
        }
        return $response;{

        }
    }

    /**
     * @param \Swift_Mailer $mailer
     * @param Request $request
     * @param EventRepository $repoEvent
     * @Route("/send-invitation/event/{event_id}/", name="send_invitation_event")
     * @return Response
     */
    public function sendNewsLetter($event_id, \Swift_Mailer $mailer, Request $request, EventRepository $repoEvent, RouterInterface $router){
        $event = $repoEvent->find($event_id);
        $users = [];
        $communaute = $event->getCommunaute();
        
        foreach ($communaute->getMembres() as $member){
            $users [] = $member->getUser();
        }
        $url = 'https://'.$router->getContext()->getHost();
        foreach ($users as $user){
            $message = (new \Swift_Message($event->getTitle()))
                ->setFrom($communaute->getUser()->getEmail())
                ->setTo(trim($user->getEmail()))
                ->setBody($this->renderView('emails/invitation_event.html.twig',[
                    'event'         => $event,
                    'communaute'    => $communaute
                ]));
            $mailer->send($message);
        }
        return $this->redirectToRoute('voir_detail_event',['id'=>$event->getId()]);
    }
    /**
     * @Route("/calendars/event/load_nbr_participant", name = "load_nbr_participant")
     */
    public function load_nbr_participant(Request $request, EventRepository $repoEvent)
    {
        $response = new Response();

        $event = $repoEvent->find($request->get('event_id'));

        $participant = $event->getPersonParticipants();

        if($participant){
            $val = count($participant);
            $data = json_encode($val);
            $response->headers->set('Content-Type', 'application/json');
            $response->setContent($data);
        }else{
            $val = 0;
            $data = json_encode($val);
            $response->headers->set('Content-Type', 'application/json');
            $response->setContent($data);
        }
        return $response;
    }

    /**
     * @Route("/calendars/event/test_actuel_mail", name = "test_actuel_mail")
     */
    public function test_actuel_mail(Request $request, EventRepository $repoEvent)
    {
        $response = new Response();

        $event = $repoEvent->find($request->get('event_id'));
        $email = $request->get('mail');
        $participants = $event->getPersonParticipants();
        $val = "no";
        foreach($participants as $participant){
            $son_mail = $participant->getEmail();
            if($son_mail == $email){
                $val = "yes";
                break;
            }
        }
                $data = json_encode($val);
                $response->headers->set('Content-Type', 'application/json');
                $response->setContent($data);
        
        
        return $response;
    }
}