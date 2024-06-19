<?php

namespace App\Entity;

use App\Repository\LogCongesRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: LogCongesRepository::class)]
class LogConges
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'logConges')]
    private ?Conges $conges = null;

    #[ORM\ManyToOne(inversedBy: 'logConges')]
    private ?User $User = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $date = null;

    #[ORM\Column(length: 255)]
    private ?string $type = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $detail = null;

    #[ORM\ManyToOne(inversedBy: 'logCongesInitiative')]
    private ?User $userInitiative = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getConges(): ?Conges
    {
        return $this->conges;
    }

    public function setConges(?Conges $conges): static
    {
        $this->conges = $conges;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->User;
    }

    public function setUser(?User $User): static
    {
        $this->User = $User;

        return $this;
    }

    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(\DateTimeInterface $date): static
    {
        $this->date = $date;

        return $this;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): static
    {
        $this->type = $type;

        return $this;
    }

    public function getDetail(): ?string
    {
        return $this->detail;
    }

    public function setDetail(?string $detail): static
    {
        $this->detail = $detail;

        return $this;
    }

    public function getUserInitiative(): ?User
    {
        return $this->userInitiative;
    }

    public function setUserInitiative(?User $userInitiative): static
    {
        $this->userInitiative = $userInitiative;

        return $this;
    }
}
