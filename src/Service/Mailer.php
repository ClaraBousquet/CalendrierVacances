<?php
namespace App\Service;

use App\Entity\User;
use Symfony\Component\Mime\Email;
use App\Repository\UserRepository;
use Symfony\Component\Mime\Address;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class Mailer extends AbstractController
{
    private MailerInterface $mailer;
    private $userRepository;
    private MakeICS $makeICS;


    public function __construct(MailerInterface $mailer,UserRepository $userRepository, MakeICS $makeICS)
    {
        $this->mailer = $mailer;
        $this->userRepository = $userRepository;
        $this->makeICS = $makeICS;

    }

    public function sendCongesRequestEmail(User $user, $conge)
    {



        $email = (new TemplatedEmail())
            ->from(new Address('vacances@adeo-informatique.com', 'Vacances'))
            ->to(new Address($user->getEmail()))
            ->subject('Nouvelle demande de congé')
            ->htmlTemplate('email/conge_demande.html.twig', ['conge'=>$conge])
            ->context(['conge'=> $conge]);

            $this->mailer->send($email);
    }

    public function sendCongesStatusUpdateEmail(User $user, $conge)
    {
        
        $email = (new TemplatedEmail())
            ->from(new Address('vacances@adeo-informatique.com', 'Vacances'))
            ->to(new Address($user->getEmail()))
            ->subject('Mise à jour du statut de votre demande de congé')
            ->htmlTemplate('email/conge_notification.html.twig', ['conge'=>$conge])
            ->context(['conge'=> $conge]);

        if ($conge->getStatut() == 'Accepté') {
            $email->attachFromPath($this->makeICS->getICS($conge->getStartDate(), $conge->getEndDate(), $conge->getDemiJournee()),null, 'text/calendar');
        }

        $this->mailer->send($email);
    }

    public function sendCongesCancel (User $user, $conge)
    {
        $email = (new TemplatedEmail())
            ->from(new Address('vacances@adeo-informatique.com', 'Vacances'))
            ->to(new Address($user->getEmail()))
            ->subject('Annulation de congé')
            ->htmlTemplate('email/conge_cancel.html.twig', ['conge'=>$conge])
            ->context(['conge'=> $conge]);

            $this->mailer->send($email);

    }


public function sendCongesStatusAdmin(User $user, $conge)
{
    $admins = $this->userRepository->findAllAdmins();
    
    $adminEmails = array_map(fn($admin) => new Address($admin->getEmail()), $admins); 

    // Créer un nouvel email
    $email = (new TemplatedEmail())
        ->from(new Address('vacances@adeo-informatique.com', 'Vacances'))
        ->subject('Nouvelle demande de congé')
        ->htmlTemplate('email/conge_notification_admin.html.twig')
        ->context(['conge' => $conge]);

    foreach ($adminEmails as $adminEmail) {
        $email->addTo($adminEmail);
    }

    $this->mailer->send($email);
}


    public function sendEmailCancel(User $user, $conge): void
    {
        $admins = $this->userRepository->findAllAdmins();
        $adminEmails = array_map(fn($admin) => new Address($admin->getEmail()), $admins); 

        $email = (new TemplatedEmail())
            ->from(new Address('vacances@adeo-informatique.com', 'Vacances'))
            ->subject('Demande dannulation de congé')
            ->htmlTemplate('email/conge_annulation.html.twig')
            ->context(['conge' => $conge]);

        foreach ($adminEmails as $adminEmail) {
            $email->addTo($adminEmail);
    }

        $this->mailer->send($email);
    }


public function sendEmail(  User $user, $conge ): void
{
        $admins = $this->userRepository->findAllAdmins();
        $adminEmails = array_map(fn($admin) => new Address($admin->getEmail()), $admins); 

    
    $email = (new TemplatedEmail())
        ->from(new Address('vacances@adeo-informatique.com', 'Vacances'))
        ->subject('Demande d\'annulation de congé')
        ->htmlTemplate('email/conge_annulation.html.twig')
        ->context(['conge' => $conge]);

          foreach ($adminEmails as $adminEmail) {
            $email->addTo($adminEmail);
    }

    $this->mailer->send($email);
}

}