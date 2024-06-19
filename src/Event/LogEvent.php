<?php

namespace App\Event;

use App\Entity\User;
use Symfony\Contracts\EventDispatcher\Event;

class LogEvent extends Event
{
    public object $entity;
    public User $user;
    public $element;

    public function __construct(object $entity, User $user, $element = null)
    {
        $this->entity = $entity;
        $this->user = $user;
        $this->element = $element;
    }

    public function getEntity(): object
    {
        return $this->entity;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function getElement()
    {
        return $this->element;
    }
}