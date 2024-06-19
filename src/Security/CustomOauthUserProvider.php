<?php

namespace App\Security;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use HWI\Bundle\OAuthBundle\OAuth\Response\UserResponseInterface;
use HWI\Bundle\OAuthBundle\Security\Core\User\OAuthAwareUserProviderInterface;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

class CustomOauthUserProvider implements OAuthAwareUserProviderInterface, UserProviderInterface
{
    public function __construct(
        private EntityManagerInterface  $entityManager,
        private UserRepository $userRepository
    ) {
    }
    public function loadUserByOAuthUserResponse(UserResponseInterface $response)
    {
        $data = $response->getData();
        //recupere le token et User Name
        $email = $data['preferred_username'];
        $tokenAzure = $data['sub'];

        $user = $this->userRepository->findOneBy(["email"=>$email]);
//
//        if($user && !$user->getAzure()){
//            $user->setAzure($tokenAzure);
//            $this->entityManager->flush();
//        }

        return $user;
    }

    public function refreshUser(UserInterface $user): UserInterface
    {
        if (!$user instanceof User) {
            throw new UnsupportedUserException(sprintf('Invalid user class "%s".', get_class($user)));
        }
        // Return a User object after making sure its data is "fresh".
        // Or throw a UserNotFoundException if the user no longer exists.
        return $user;
    }

    public function supportsClass(string $class): bool
    {
        return User::class === $class || is_subclass_of($class, User::class);
    }

    public function loadUserByIdentifier(string $identifier):UserInterface
    {
        $user = $this->userRepository->findOneBy(['username'=>$identifier]);
        if(!$user){
            throw new UserNotFoundException('Game Over');
        }

        return $user;
    }

    public function loadUserByUsername(string $username)
    {
        // TODO: Implement loadUserByUsername() method.
    }
}