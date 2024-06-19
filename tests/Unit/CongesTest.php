<?php

test('Create Object Conges', function () {

    $user = new \App\Entity\User();
    $user->setUsername('Romain');
    $user->setIsCadre(false);

    $date = new DateTime('2024-05-22');

    $log = (new \App\Entity\LogConges())
        ->setUser($user)
        ->setDate($date)
        ->setType('addConges')
        ->setDetail(null)
    ;

    $conges = (new \App\Entity\Conges())
        ->setUser($user)
        ->setStartDate($date)
        ->setEndDate($date)
        ->addLogConge($log)
        ->setRemove(false)
    ;

    expect($conges)->toBeInstanceOf(\App\Entity\Conges::class)
        ->and($conges->getUser())->toBe($user)
        ->and($conges->getStartDate()->format('Y-m-d'))->toBe('2024-05-22')
        ->and($conges->getEndDate()->format('Y-m-d'))->toBe('2024-05-22')
        ->and($conges->getId())->toBe($conges->getId())
        ->and($conges->getLogConges()[0])->toBe($log)
        ->and($conges->isRemove())->toBe(false)
    ;
});
