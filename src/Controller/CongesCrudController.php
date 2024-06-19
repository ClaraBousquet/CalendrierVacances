<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Conges;
use App\Event\LogEvent;
use App\Service\Mailer;
use App\Form\CongesType;
use App\Form\CongesStatutsType;
use App\Repository\CongesRepository;
use App\Repository\ServicesRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/conges/crud')]
class CongesCrudController extends AbstractController
{
    private EventDispatcherInterface $dispatcher;
    private EntityManagerInterface $entityManager;


    public function __construct(EntityManagerInterface $entityManager, EventDispatcherInterface $dispatcher)
    {
        $this->entityManager = $entityManager;
        $this->dispatcher = $dispatcher;
    }

    #[Route('/', name: 'app_conges_crud_index', methods: ['GET'])]
    public function index(CongesRepository $congesRepository): Response
    {

        $date = new \DateTime();

        return $this->render('conges_crud/index.html.twig', [

            'congesPasse' => $congesRepository->findPasse($date->format('Y-m-d')),
            'congesFutur' => $congesRepository->findFutur($date->format('Y-m-d')),
            'page' => 'conges',
            'userConnecter' => $this->getUser(),
        ]);
    }

    #[Route('/new', name: 'app_conges_crud_new', methods: ['GET', 'POST'])]
    public function new(Request $request, Mailer $mailer): Response
    {
        $conge = new Conges();
        $conge->setStatut('en attente');
        $conge->setUser($this->getUser());

        $timezone = new \DateTimeZone('Europe/Paris');
        $conge->setCreatedAt(new \DateTimeImmutable('now', $timezone));

        $form = $this->createForm(CongesType::class, $conge);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Récupérer les heures et minutes 
            $halfDayCheckbox = $request->request->get('halfDayCheckbox');
            $halfDayOption = $request->request->get('halfDayOption');


            if ($halfDayCheckbox != null) {
                if ($halfDayOption == 'Matin') {
                    $conge->setDemiJournee('Matin');
                } elseif ($halfDayOption == 'Après-midi') {
                    $conge->setDemiJournee('Aprés-midi');
                } else {
                    $conge->setDemiJournee(null);
                }
            }


            // vérif date de début date de fin
            if ($conge->getEndDate() < $conge->getStartDate()) {
                $this->addFlash('error', 'La date de début doit être antérieure à la date de fin.');
                return $this->redirectToRoute('app_conges_crud_new', ['showFlash' => true]);
            }
            // Vérifier si un congé existe déjà 
            $existingConges = $this->entityManager->getRepository(Conges::class)
                ->createQueryBuilder('c')
                ->where('c.user = :user')
                ->andWhere('(:startDate BETWEEN c.startDate AND c.endDate) OR (:endDate BETWEEN c.startDate AND c.endDate) OR (c.startDate BETWEEN :startDate AND :endDate) OR (c.endDate BETWEEN :startDate AND :endDate)')
                ->setParameter('user', $this->getUser())
                ->setParameter('startDate', $conge->getStartDate())
                ->setParameter('endDate', $conge->getEndDate())
                ->getQuery()
                ->getResult();

            if (!empty($existingConges)) {
                $this->addFlash('error', 'Vous avez déjà un congé à cette date');
                return $this->redirectToRoute('app_home', ['showFlash' => true]);
            }

            try {
                $this->entityManager->persist($conge);
                $this->entityManager->flush();

                $logEvent = new LogEvent($conge, $this->getUser());
                $this->dispatcher->dispatch($logEvent, 'add.conges');

                $mailer->sendCongesRequestEmail($this->getUser(), $conge, 'en attente');
                $mailer->sendCongesStatusAdmin($this->getUser(), $conge);

                return $this->redirectToRoute('app_home', [], Response::HTTP_SEE_OTHER);
            } catch (\Exception $e) {
            }
        }
        return $this->redirectToRoute('app_home', [], Response::HTTP_SEE_OTHER);
    }


    #[Route('/api/current_user', name: 'api_current_user', methods: ['GET'])]
    public function getCurrentUser(): JsonResponse
    {
        $user = $this->getUser();
        if (!$user) {
            return $this->json(['error' => 'User not authenticated'], JsonResponse::HTTP_UNAUTHORIZED);
        }

        return $this->json([
            'id' => $user->getId(),
            'username' => $user->getUsername(),
            'roles' => $user->getRoles(),
        ]);
    }


    #[Route('/api/conges', name: 'api_conges')]
    public function getConges(CongesRepository $congesRepository, ServicesRepository $servicesRepository): JsonResponse
    {
        $user = $this->getUser();
        $data = [];
        $conges = [];
        if ($this->isGranted('ROLE_ADMIN')) {
            $conges = $congesRepository->findAll();
        } else {
            $serviceId = $user->getService()->getId();
            $conges = $congesRepository->findByServiceId($serviceId);
        }

        $allConges = [];
        foreach ($conges as $conge) {
            if ($conge->getDemiJournee() != NULL) {
                $title = $conge->getUser()->getUsername(). ' (' . $conge->getDemiJournee() . ') - ' . $conge->getStatut();

            } else {
                $title = $conge->getUser()->getUsername() . ' - ' . $conge->getStatut();
            }

            $allConges[] = [
                'id' => $conge->getId(),
                'title' => $title,
                'start' => $conge->getStartDate()->format('Y-m-d') . 'T00:00:00Z',
                'end' => $conge->getEndDate()->format('Y-m-d') . 'T23:59:59Z',
                'statut' => $conge->getStatut(),
                'color' => $conge->getUser()->getService()->getCouleur(),
                'service' => $conge->getUser()->getService()->getLabel(),
                'allDay' => true,
            ];
        }

        $allServices = $servicesRepository->findAll();
        $services = [];

        foreach ($allServices as $service) {
            $services[] = [
                'id' => $service->getId(),
                'label' => $service->getLabel(),
                'color' => $service->getCouleur(),
            ];
        }

        $user = [
            'id' => $user->getId(),
            'username' => $user->getUsername(),
            'service' => $user->getService()->getLabel(),
            'roles' => $user->getRoles(),
        ];

        $data = [
            "conges" => $allConges,
            "services" => $services,
            "user" => $user
        ];
        return $this->json($data);
    }



    #[Route('/api/conges/{dateSelect}', name: 'api_conges_detail', methods: ['GET'])]
    public function getCongesDetail($dateSelect, CongesRepository $congesRepository): JsonResponse
    {
        $userConnected = $this->getUser();

        if ($this->isGranted('ROLE_ADMIN')) {
            $conges = $congesRepository->findByDate($dateSelect);
        } else {
            $conges = $congesRepository->findByDateService($dateSelect, $userConnected->getService());
        }
        $data = [];
        foreach ($conges as $conge) {
            $data[] = [
                'id' => $conge->getId(),
                'title' => $conge->getUser()->getUsername(),
                'start' => $conge->getStartDate()->format('Y-m-d'),
                'end' => $conge->getEndDate()->format('Y-m-d'),
                'statut' => $conge->getStatut(),
                'color' => $conge->getUser()->getService()->getCouleur(),
            ];
        }
        return $this->json($data);
    }



    #[Route('/api/services', name: 'api_services', methods: ['GET'])]
    public function getServices(ServicesRepository $servicesRepository): JsonResponse
    {
        try {
            $services = $servicesRepository->findAll();
            $data = [];

            foreach ($services as $service) {
                $data[] = [
                    'id' => $service->getId(),
                    'name' => $service->getLabel(),
                ];
            }

            return new JsonResponse($data);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => 'Erreur lors de la récupération des services'], 500);
        }
    }




    #[Route('/edit/{id}', name: 'app_conges_crud_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Conges $conge): Response
    {
        $user = $this->getUser();

        if ($conge->getUser()->getId() !== $user->getId()) {
            $this->addFlash('error', 'Vous ne pouvez pas éditer ce congé.');
            return $this->redirectToRoute('app_conges_crud_index');
        } else {
            $form = $this->createForm(CongesType::class, $conge);
            $form->handleRequest($request);
            if ($form->isSubmitted() && $form->isValid()) {
                try {
                    $this->entityManager->flush();
                    $logEvent = new LogEvent($conge, $this->getUser());
                    $this->dispatcher->dispatch($logEvent, 'edit.conges');
                } catch (\Exception $e) {
                }
                return $this->redirectToRoute('app_conges_crud_index', [], Response::HTTP_SEE_OTHER);
            }

            return $this->render('conges_crud/edit.html.twig', [
                'page' => 'conges',
                'conge' => $conge,
                'form' => $form,
                'userConnecter' => $this->getUser(),
            ]);
        }
    }


    #[Route('/{id}/delete', name: 'app_conges_crud_delete', methods: ['POST'])]
    public function delete(Request $request, Conges $conge,): Response
    {
        if ($this->isCsrfTokenValid('delete' . $conge->getId(), $request->getPayload()->get('_token'))) {
            try {
                $conge->setRemove(true);
                $this->entityManager->flush();
                $logEvent = new LogEvent($conge, $this->getUser());
                $this->dispatcher->dispatch($logEvent, 'remove.conges');
            } catch (\Exception $e) {
            }
        }
        return $this->redirectToRoute('app_conges_crud_index', [], Response::HTTP_SEE_OTHER);
    }


    #[Route('/refuse/{id}', name: 'refuse_conge')]
    #[IsGranted('ROLE_ADMIN')]
    public function refuseConge(Conges $conge, Mailer $mailer): Response
    {
        $conge->setStatut('Refusé');
        $this->entityManager->persist($conge);
        $this->entityManager->flush();

        $logEvent = new LogEvent($conge, $this->getUser());
        $this->dispatcher->dispatch($logEvent, 'refuse.conge');

        $mailer->sendCongesStatusUpdateEmail($conge->getUser(), $conge);
        return $this->redirectToRoute('app_conges_demande', [], Response::HTTP_SEE_OTHER);
    }



    #[Route('/validate/{id}', name: 'validate_conge')]
    #[IsGranted('ROLE_ADMIN')]
    public function validateConge(Conges $conge, Mailer $mailer): Response
    {
        $conge->setStatut('Accepté');
        $this->entityManager->persist($conge);
        $this->entityManager->flush();

        $logEvent = new LogEvent($conge, $this->getUser());
        $this->dispatcher->dispatch($logEvent, 'validate.conge');

        $mailer->sendCongesStatusUpdateEmail($conge->getUser(), $conge);
        return $this->redirectToRoute('app_conges_demande', [], Response::HTTP_SEE_OTHER);
    }



    #[Route('/cancel/{id}', name: 'cancel_conge')]
    #[IsGranted('ROLE_ADMIN')]
    public function cancelConge(Conges $conge, Mailer $mailer): Response
    {
        $conge->setStatut('Annulé');
        $this->entityManager->persist($conge);
        $this->entityManager->flush();

        $logEvent = new LogEvent($conge, $this->getUser());
        $this->dispatcher->dispatch($logEvent, 'cancel.conge');

        $mailer->sendCongesCancel($conge->getUser(), $conge);

        $this->addFlash('info', 'Congé annulé avec succés.');

        return $this->redirectToRoute('app_conges_demande', [], Response::HTTP_SEE_OTHER);
    }



    #[Route('/request-cancel/{id}', name: 'request_cancel_conge')]
    public function requestCancelConge(Mailer $mailer, ?Conges $conge): Response
    {
        $user = $conge->getUser();
        $mailer->sendEmail($user, $conge);

        $this->addFlash('info', 'la demande d\'annulation a bien été envoyé.');

        return $this->redirectToRoute('app_home');
    }
}
