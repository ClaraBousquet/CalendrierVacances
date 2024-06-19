<?php

namespace App\Entity;

use App\Repository\CongesRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CongesRepository::class)]
class Conges
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private \DateTimeInterface $startDate;

    #[ORM\Column(type: Types::DATETIME_MUTABLE,)]
    private \DateTimeInterface $endDate;

    #[ORM\ManyToOne(inversedBy: 'conges')]
    private User $user;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $Statut = null;

    #[ORM\ManyToOne(inversedBy: 'conges')]
    private ?Type $type = null;

    /**
     * @var Collection<int, LogConges>
     */
    #[ORM\OneToMany(targetEntity: LogConges::class, mappedBy: 'conges')]
    private Collection $logConges;

    #[ORM\Column]
    private ?bool $remove = false;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $demiJournee = null;

    public function __construct()
    {
        $this->logConges = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getStartDate(): \DateTimeInterface
    {
        return $this->startDate;
    }

    public function setStartDate(\DateTimeInterface $startDate): static
    {
        $this->startDate = $startDate;

        return $this;
    }

    public function getEndDate(): \DateTimeInterface
    {
        return $this->endDate;
    }

    public function setEndDate(\DateTimeInterface $endDate): static
    {
        $this->endDate = $endDate;

        return $this;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function setUser(User $user): static
    {
        $this->user = $user;

        return $this;
    }

    public function getStatut(): ?string
    {
        return $this->Statut;
    }

    public function setStatut(?string $Statut): static
    {
        $this->Statut = $Statut;

        return $this;
    }

    public function getType(): ?Type
    {
        return $this->type;
    }

    public function setType(?Type $type): static
    {
        $this->type = $type;

        return $this;
    }

    /**
     * @return Collection<int, LogConges>
     */
    public function getLogConges(): Collection
    {
        return $this->logConges;
    }

    public function addLogConge(LogConges $logConge): static
    {
        if (!$this->logConges->contains($logConge)) {
            $this->logConges->add($logConge);
            $logConge->setConges($this);
        }

        return $this;
    }

    public function removeLogConge(LogConges $logConge): static
    {
        if ($this->logConges->removeElement($logConge)) {
            // set the owning side to null (unless already changed)
            if ($logConge->getConges() === $this) {
                $logConge->setConges(null);
            }
        }

        return $this;
    }

    public function isRemove(): ?bool
    {
        return $this->remove;
    }

    public function setRemove(bool $remove): static
    {
        $this->remove = $remove;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(?\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getDemiJournee(): ?string
    {
        return $this->demiJournee;
    }

    public function setDemiJournee(?string $demiJournee): static
    {
        $this->demiJournee = $demiJournee;

        return $this;
    }
}
