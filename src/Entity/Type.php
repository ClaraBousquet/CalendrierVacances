<?php

namespace App\Entity;

use App\Repository\TypeRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TypeRepository::class)]
class Type
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $label = null;

    /**
     * @var Collection<int, Conges>
     */
    #[ORM\OneToMany(targetEntity: Conges::class, mappedBy: 'type')]
    private Collection $conges;

    #[ORM\Column]
    private ?bool $requiresIsCadre = null;

    #[ORM\Column]
    private ?bool $is_restricted_user = null;

    /**
     * @var Collection<int, User>
     */
    #[ORM\ManyToMany(targetEntity: User::class, inversedBy: 'types')]
    private Collection $restricted_user;

    public function __construct()
    {
        $this->conges = new ArrayCollection();
        $this->restricted_user = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getLabel(): ?string
    {
        return $this->label;
    }

    public function setLabel(?string $label): static
    {
        $this->label = $label;

        return $this;
    }

    /**
     * @return Collection<int, Conges>
     */
    public function getConges(): Collection
    {
        return $this->conges;
    }

    public function addConge(Conges $conge): static
    {
        if (!$this->conges->contains($conge)) {
            $this->conges->add($conge);
            $conge->setType($this);
        }

        return $this;
    }

    public function removeConge(Conges $conge): static
    {
        if ($this->conges->removeElement($conge)) {
            if ($conge->getType() === $this) {
                $conge->setType(null);
            }
        }

        return $this;
    }

    public function isRequiresIsCadre(): ?bool
    {
        return $this->requiresIsCadre;
    }

    public function setRequiresIsCadre(bool $requiresIsCadre): static
    {
        $this->requiresIsCadre = $requiresIsCadre;

        return $this;
    }

    public function isRestrictedUser(): ?bool
    {
        return $this->is_restricted_user;
    }

    public function setIsRestrictedUser(bool $is_restricted_user): static
    {
        $this->is_restricted_user = $is_restricted_user;

        return $this;
    }

    /**
     * @return Collection<int, User>
     */
    public function getRestrictedUser(): Collection
    {
        return $this->restricted_user;
    }

    public function addRestrictedUser(User $restrictedUser): static
    {
        if (!$this->restricted_user->contains($restrictedUser)) {
            $this->restricted_user->add($restrictedUser);
        }

        return $this;
    }

    public function removeRestrictedUser(User $restrictedUser): static
    {
        $this->restricted_user->removeElement($restrictedUser);

        return $this;
    }
}
