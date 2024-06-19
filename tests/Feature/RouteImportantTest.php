<?php

beforeEach(fn () => clearDatabase());

it('access route login', function () {
    $client = static::createClient();

    $url = $_ENV["URL_DNS_SERVER"] . "/login";
    $crawler = $client->request('GET', $url);

    $this->assertTrue($client->getResponse()->isSuccessful());
    $this->assertCount(1, $crawler->filter('h3:contains("Se connecter")'));
});

//it('authenticate user', function () {
//    $client = static::createClient();
//
//    $user = createUser('Romain', []);
//    save($user);
//
//    $this->login($client, $user);
//
//    $url = $_ENV["URL_DNS_SERVER"] . "/conges/crud/";
//    $crawler = $client->request('GET', $url);
//
//    dd($crawler);
//
//    $this->assertTrue($client->getResponse()->isSuccessful());
//    $this->assertCount(1, $crawler->filter('h1:contains("Liste des congés")'));
//});
