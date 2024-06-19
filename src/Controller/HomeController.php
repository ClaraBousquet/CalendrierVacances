<?php

namespace App\Controller;


use App\Entity\Conges;
use App\Event\LogEvent;
use App\Form\CongesType;
use App\Form\UserSelfType;
use App\Repository\CongesRepository;
use App\Service\Mailer;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/')]
class HomeController extends AbstractController
{
    #[Route(name: 'app_home')]
    public function index(Request $request, EntityManagerInterface $entityManager, EventDispatcherInterface $dispatcher, Mailer $mailer): Response
    {
        $conge = new Conges();
        $conge->setStatut('en attente');
        $conge->setUser($this->getUser());

        $form = $this->createForm(CongesType::class, $conge, [
            'action' => $this->generateUrl('app_conges_crud_new'),
            'method' => 'POST',
        ]);

        return $this->render('home/index.html.twig', [
            'page' => 'calendrier',
            'controller_name' => 'Calendrier Vacances v2',
            'form' => $form->createView(),
            'userConnecter' => $this->getUser(),
        ]);
    }

    #[Route('/historique', name: 'app_show_log')]
    public function showLog(): Response
    {
        $user = $this->getUser();

        return $this->render('home/log.html.twig', [
            'page' => 'calendrier',
            'userConnecter' => $this->getUser(),
            'logConges' => $user->getLogConges()->toArray(),
        ]);
    }
}
