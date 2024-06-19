<?php

beforeEach(fn() => clearDatabase());

it('Create conges with form calendar and add log', function (){
    //ARRANGE
    $date = new DateTime();

    $interval = DateInterval::createFromDateString('1 day');
    $dateEnd = clone $date;
    $dateEnd->add($interval);

    $service = new App\Entity\Services();
    $service->setLabel('devthebest');
    $service->setCouleur('#fff');
    save($service);

    $user = createUser('Romain',[], $service);
    save($user);

    $type = new App\Entity\Type();
    $type->setLabel('Journee');
    $type->setIsRestrictedUser(false);
    $type->setRequiresIsCadre(false);
    save($type);

    $client = static::createClient();
    $url = $_ENV["URL_DNS_SERVER"];
    //====================================================================

    //ACT
    //connecter le client
    $this->login($client, $user);
    //Requete
    $crawler = $client->request('GET', $url);
    $stringDateStart = $date->format('Y-m-d');
    $stringDateEnd = $dateEnd->format('Y-m-d');

    $client->submitForm('Créer Congé', [
        'conges[startDate]' => $stringDateStart,
        'conges[endDate]' => $stringDateEnd,
        'conges[type]' => $type->getId(),
    ]);

    // Test Conges
    $congesArray = repository(\App\Entity\Conges::class)->findBy(["user"=>$user]);
    expect($congesArray[0])->toBeInstanceOf(\App\Entity\Conges::class)
        ->and($congesArray[0]->getStartDate()->format('Y-m-d'))->toEqual($stringDateStart)
        ->and($congesArray[0]->getEndDate()->format('Y-m-d'))->toEqual($stringDateEnd)
        ->and($congesArray[0]->getType()->getLabel())->toEqual('Journee')
    ;

    // Test log
    $logArray = repository(\App\Entity\LogConges::class)->findBy(["conges"=>$congesArray[0]]);
    expect($logArray[0])->toBeInstanceOf(\App\Entity\LogConges::class)
        ->and($logArray[0]->getDate()->format('Y-m-d H:i'))->toEqual($date->format('Y-m-d H:i'))
        ->and($logArray[0]->getType())->toEqual('addConges')
        ->and($logArray[0]->getUser())->toEqual($user)
    ;
});


it('Valid conges', function (){
    //ARRANGE
    $date = new DateTime();

    $interval = DateInterval::createFromDateString('1 day');
    $dateEnd = clone $date;
    $dateEnd->add($interval);

    $service = new App\Entity\Services();
    $service->setLabel('devthebest');
    $service->setCouleur('#fff');
    save($service);

    $user = createUser('Romain',[], $service);
    save($user);

    $userAdmin = createUser('Aurel',["ROLE_ADMIN"], $service);
    save($userAdmin);

    $type = new App\Entity\Type();
    $type->setLabel('Journee');
    $type->setIsRestrictedUser(false);
    $type->setRequiresIsCadre(false);
    save($type);

    $client = static::createClient();
    $url = $_ENV["URL_DNS_SERVER"];
    //====================================================================

    //ACT
    //connecter le client
    $this->login($client, $user);

    //Requete
    $crawler = $client->request('GET', $url);
    $stringDateStart = $date->format('Y-m-d');
    $stringDateEnd = $dateEnd->format('Y-m-d');

    $client->submitForm('Créer Congé', [
        'conges[startDate]' => $stringDateStart,
        'conges[endDate]' => $stringDateEnd,
        'conges[type]' => $type->getId(),
    ]);

    // Connexion avec l'administrateur
    $url = $_ENV["URL_DNS_SERVER"] . "/admin/demande";

    $this->login($client, $userAdmin);
    $crawler = $client->request('GET', $url);

    $this->assertTrue($client->getResponse()->isSuccessful());
    $this->assertCount(1, $crawler->filter('h1:contains("Demandes de congés")'));
    $this->assertCount(1, $crawler->filter('td:contains("Romain")'));
    $this->assertCount(1, $crawler->filter('td:contains("en attente")'));

    $button = $crawler->filter('a:contains("Accepter")')->link();
    $crawler = $client->click($button);

    $crawler = $client->request('GET', $url);

    $this->assertTrue($client->getResponse()->isSuccessful());
    $this->assertCount(1, $crawler->filter('h1:contains("Demandes de congés")'));
    $this->assertCount(1, $crawler->filter('td:contains("Romain")'));
    $this->assertCount(1, $crawler->filter('td:contains("Accepté")'));

});



it('Refused conges', function (){
    //ARRANGE
    $date = new DateTime();

    $interval = DateInterval::createFromDateString('1 day');
    $dateEnd = clone $date;
    $dateEnd->add($interval);

    $service = new App\Entity\Services();
    $service->setLabel('devthebest');
    $service->setCouleur('#fff');
    save($service);

    $user = createUser('Romain',[], $service);
    save($user);

    $userAdmin = createUser('Aurel',["ROLE_ADMIN"], $service);
    save($userAdmin);

    $type = new App\Entity\Type();
    $type->setLabel('Journee');
    $type->setIsRestrictedUser(false);
    $type->setRequiresIsCadre(false);
    save($type);

    $client = static::createClient();
    $url = $_ENV["URL_DNS_SERVER"];
    //====================================================================

    //ACT
    //connecter le client
    $this->login($client, $user);

    //Requete
    $crawler = $client->request('GET', $url);
    $stringDateStart = $date->format('Y-m-d');
    $stringDateEnd = $dateEnd->format('Y-m-d');

    $client->submitForm('Créer Congé', [
        'conges[startDate]' => $stringDateStart,
        'conges[endDate]' => $stringDateEnd,
        'conges[type]' => $type->getId(),
    ]);

    // Connexion avec l'administrateur
    $url = $_ENV["URL_DNS_SERVER"] . "/admin/demande";

    $this->login($client, $userAdmin);
    $crawler = $client->request('GET', $url);

    $this->assertTrue($client->getResponse()->isSuccessful());
    $this->assertCount(1, $crawler->filter('h1:contains("Demandes de congés")'));
    $this->assertCount(1, $crawler->filter('td:contains("Romain")'));
    $this->assertCount(1, $crawler->filter('td:contains("en attente")'));

    $button = $crawler->filter('a:contains("Refuser")')->link();
    $crawler = $client->click($button);

    $crawler = $client->request('GET', $url);

    $this->assertTrue($client->getResponse()->isSuccessful());
    $this->assertCount(1, $crawler->filter('h1:contains("Demandes de congés")'));
    $this->assertCount(1, $crawler->filter('td:contains("Romain")'));
    $this->assertCount(1, $crawler->filter('td:contains("Refusé")'));

});



it('Cancel conges', function (){
    //ARRANGE
    $date = new DateTime();

    $interval = DateInterval::createFromDateString('1 day');
    $dateEnd = clone $date;
    $dateEnd->add($interval);

    $service = new App\Entity\Services();
    $service->setLabel('devthebest');
    $service->setCouleur('#fff');
    save($service);

    $user = createUser('Romain',[], $service);
    save($user);

    $userAdmin = createUser('Aurel',["ROLE_ADMIN"], $service);
    save($userAdmin);

    $type = new App\Entity\Type();
    $type->setLabel('Journee');
    $type->setIsRestrictedUser(false);
    $type->setRequiresIsCadre(false);
    save($type);

    $client = static::createClient();
    $url = $_ENV["URL_DNS_SERVER"];
    //====================================================================

    //ACT
    //connecter le client
    $this->login($client, $user);

    //Requete
    $crawler = $client->request('GET', $url);
    $stringDateStart = $date->format('Y-m-d');
    $stringDateEnd = $dateEnd->format('Y-m-d');

    $client->submitForm('Créer Congé', [
        'conges[startDate]' => $stringDateStart,
        'conges[endDate]' => $stringDateEnd,
        'conges[type]' => $type->getId(),
    ]);

    // Connexion avec l'administrateur
    $url = $_ENV["URL_DNS_SERVER"] . "/admin/demande";

    $this->login($client, $userAdmin);
    $crawler = $client->request('GET', $url);


    $this->assertTrue($client->getResponse()->isSuccessful());
    $this->assertCount(1, $crawler->filter('h1:contains("Demandes de congés")'));
    $this->assertCount(1, $crawler->filter('td:contains("Romain")'));
    $this->assertCount(1, $crawler->filter('td:contains("en attente")'));

    $button = $crawler->filter('a:contains("Annuler")')->link();
    $crawler = $client->click($button);

    $crawler = $client->request('GET', $url);

    $this->assertTrue($client->getResponse()->isSuccessful());
    $this->assertCount(1, $crawler->filter('h1:contains("Demandes de congés")'));
    $this->assertCount(1, $crawler->filter('td:contains("Romain")'));
    $this->assertCount(1, $crawler->filter('td:contains("Annulé")'));

});
