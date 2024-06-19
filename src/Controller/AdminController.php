<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Conges;
use App\Form\CongesStatutsType;
use App\Form\UserType;
use App\Repository\CongesRepository;
use App\Repository\ServicesRepository;
use App\Repository\TypeRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin')]
#[IsGranted('ROLE_ADMIN')]
class AdminController extends AbstractController
{
    private EntityManagerInterface $entityManager;


    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    #[Route('/', name: 'app_admin')]
    public function index(): Response
    {
         $this->denyAccessUnlessGranted('ROLE_ADMIN');
        return $this->render('admin/index.html.twig', [
            'controller_name' => 'AdminController',
        ]);
    }

    #[Route('/demande', name: 'app_conges_demande')]
    #[IsGranted('ROLE_ADMIN')]
    public function demande(CongesRepository $congesRepository): Response
    {
        $conges = $congesRepository->findAll();

        $formViews = [];

        return $this->render('admin/demande.html.twig', [
            'page' => 'demande',
            'userConnecter' => $this->getUser(),
            'user' => $this->getUser(),
            'conges' => $congesRepository->findAll(),
        ]);
    }

    #[Route('/panel', name: 'app_admin_panel')]
    #[IsGranted('ROLE_ADMIN')]
    public function panel(UserRepository $userRepository, ServicesRepository $servicesRepository, TypeRepository $typeRepository): Response
    {

        return $this->render('admin/panel.html.twig', [
            'page' => 'panel',
            'userConnecter' => $this->getUser(),
            'users' => $userRepository->findAll(),
            'services' => $servicesRepository->findAll(),
            'types' => $typeRepository->findAll()
        ]);
    }

    #[Route('/{id}/edit', name: 'app_user_edit', methods: ['GET', 'POST'])]
    public function editUser(Request $request, User $user, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_admin_panel', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('user/edit.html.twig', [
            'page' => 'panel',
            'user' => $user,
            'form' => $form,
            'userConnecter' => $this->getUser(),
        ]);
    }


}
