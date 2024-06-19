<?php

namespace App\EventSubscriber;

use App\Entity\LogConges;
use App\Event\logEvent;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class LogSubscriber implements EventSubscriberInterface
{
    private EntityManagerInterface $entityManager;
    private \DateTime $dateNow;

    public function __construct(EntityManagerInterface $entityManager) {
        $this->entityManager = $entityManager;
        $timezone =  new \DateTimeZone('Europe/Paris');;
        $this->dateNow = new \DateTime('now', $timezone);
    }

    public static function getSubscribedEvents(): array
    {
        return [
            /*
                LOG Congés
                    → Ajout
                    → Modification
                    → Suppression
                    → Changement d'état
             */

            'add.conges' => 'addConges',
            'edit.conges' => 'editConges',
            'remove.conges' => 'removeConges',
            'refuse.conge' => 'refuseConge',
            'validate.conge' => 'validateConge',
            'cancel.conge' => 'cancelConge',

        ];
    }

    public function addConges(logEvent $logEvent):void
    {
        $log = new LogConges();

        $log->setConges($logEvent->getEntity());
        $log->setUser($logEvent->getEntity()->getUser());
        $log->setUserInitiative($logEvent->getUser());
        $log->setDate($this->dateNow);
        $log->setType('addConges');
        $log->setDetail(json_encode($logEvent->getElement()));

        $this->entityManager->persist($log);
        $this->entityManager->flush();
    }

    public function editConges(logEvent $logEvent):void
    {
        $log = new LogConges();

        $log->setConges($logEvent->getEntity());
        $log->setUser($logEvent->getEntity()->getUser());
        $log->setUserInitiative($logEvent->getUser());
        $log->setDate($this->dateNow);
        $log->setType('editConges');
        $log->setDetail(json_encode($logEvent->getElement()));

        $this->entityManager->persist($log);
        $this->entityManager->flush();
    }

    public function removeConges(logEvent $logEvent):void
    {
        $log = new LogConges();

        $log->setConges($logEvent->getEntity());
        $log->setUser($logEvent->getEntity()->getUser());
        $log->setUserInitiative($logEvent->getUser());
        $log->setDate($this->dateNow);
        $log->setType('removeConges');
        $log->setDetail(json_encode($logEvent->getElement()));

        $this->entityManager->persist($log);
        $this->entityManager->flush();
    }

    public function refuseConge(logEvent $logEvent):void
    {
        $log = new LogConges();


        $log->setConges($logEvent->getEntity());
        $log->setUser($logEvent->getEntity()->getUser());
        $log->setUserInitiative($logEvent->getUser());
        $log->setDate($this->dateNow);
        $log->setType('refuseConge');
        $log->setDetail(json_encode($logEvent->getElement()));


        $this->entityManager->persist($log);
        $this->entityManager->flush();
    }

    public function validateConge(logEvent $logEvent):void
    {
        $log = new LogConges();

        $log->setConges($logEvent->getEntity());
        $log->setUser($logEvent->getEntity()->getUser());
        $log->setUserInitiative($logEvent->getUser());
        $log->setDate($this->dateNow);
        $log->setType('validateConge');
        $log->setDetail(json_encode($logEvent->getElement()));

        $this->entityManager->persist($log);
        $this->entityManager->flush();
    }

    public function cancelConge(logEvent $logEvent):void
    {
        $log = new LogConges();

        $log->setConges($logEvent->getEntity());
        $log->setUser($logEvent->getEntity()->getUser());
        $log->setUserInitiative($logEvent->getUser());
        $log->setDate($this->dateNow);
        $log->setType('cancelConge');
        $log->setDetail(json_encode($logEvent->getElement()));

        $this->entityManager->persist($log);
        $this->entityManager->flush();
    }
}