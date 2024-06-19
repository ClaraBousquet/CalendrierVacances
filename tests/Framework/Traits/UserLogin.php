<?php

namespace App\Tests\Framework\Traits;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Component\BrowserKit\Cookie;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

trait UserLogin
{
    /**
     * Creation de la session du client avec le token de connection
     * @param KernelBrowser $client
     * @param User $user
     */
    public function login(KernelBrowser $client,User $user): void
    {
        $client->loginUser($user);

    }
}