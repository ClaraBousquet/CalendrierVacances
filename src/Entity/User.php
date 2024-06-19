<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\UniqueConstraint(name: 'UNIQ_IDENTIFIER_USERNAME', fields: ['username'])]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 180)]
    private ?string $username = null;

    /**
     * @var list<string> The user roles
     */
    #[ORM\Column]
    private array $roles = [];

    /**
     * @var string The hashed password
     */
    #[ORM\Column]
    private ?string $password = null;

    /**
     * @var Collection<int, Conges>
     */
    #[ORM\OneToMany(targetEntity: Conges::class, mappedBy: 'user')]
    private Collection $conges;

    /**
     * @var Collection<int, LogConges>
     */
    #[ORM\OneToMany(targetEntity: LogConges::class, mappedBy: 'User')]
    private Collection $logConges;

    #[ORM\Column]
    private ?bool $isCadre = null;

    #[ORM\Column(length: 255)]
    private ?string $email = null;

    #[ORM\ManyToOne(inversedBy: 'users')]
    private ?Services $Service = null;

    /**
     * @var Collection<int, Type>
     */
    #[ORM\ManyToMany(targetEntity: Type::class, mappedBy: 'restricted_user')]
    private Collection $types;

    /**
     * @var Collection<int, LogConges>
     */
    #[ORM\OneToMany(targetEntity: LogConges::class, mappedBy: 'userInitiative')]
    private Collection $logCongesInitiative;

    public function __construct()
    {
        $this->conges = new ArrayCollection();
        $this->logConges = new ArrayCollection();
        $this->types = new ArrayCollection();
        $this->logCongesInitiative = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function setUsername(string $username): static
    {
        $this->username = $username;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->username;
    }

    /**
     * @see UserInterface
     *
     * @return list<string>
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    /**
     * @param list<string> $roles
     */
    public function setRoles(array $roles): static
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials(): void
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    /**
     * @return Collection<int, Conges>
     */
    public function getConge(): Collection
    {
        return $this->conges;
    }

    public function addConge(Conges $conge): static
    {
        if (!$this->conges->contains($conge)) {
            $this->conges->add($conge);
            $conge->setUser($this);
        }

        return $this;
    }

    // public function removeConge(Conges $conge): static
    // {
    //     if ($this->conges->removeElement($conge)) {
    //         // set the owning side to null (unless already changed)
    //         if ($conge->getUser() === $this) {
    //             $conge->setUser(null);
    //         }
    //     }

    //     return $this;
    // }

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
            $logConge->setUser($this);
        }

        return $this;
    }

    public function removeLogConge(LogConges $logConge): static
    {
        if ($this->logConges->removeElement($logConge)) {
            // set the owning side to null (unless already changed)
            if ($logConge->getUser() === $this) {
                $logConge->setUser(null);
            }
        }

        return $this;
    }

    public function isCadre(): ?bool
    {
        return $this->isCadre;
    }

    public function setIsCadre(bool $isCadre): static
    {
        $this->isCadre = $isCadre;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    public function getService(): ?Services
    {
        return $this->Service;
    }

    public function setService(?Services $Service): static
    {
        $this->Service = $Service;

        return $this;
    }

    /**
     * @return Collection<int, Type>
     */
    public function getTypes(): Collection
    {
        return $this->types;
    }

    public function addType(Type $type): static
    {
        if (!$this->types->contains($type)) {
            $this->types->add($type);
            $type->addRestrictedUser($this);
        }

        return $this;
    }

    public function removeType(Type $type): static
    {
        if ($this->types->removeElement($type)) {
            $type->removeRestrictedUser($this);
        }

        return $this;
    }

    /**
     * @return Collection<int, LogConges>
     */
    public function getLogCongesInitiative(): Collection
    {
        return $this->logCongesInitiative;
    }

    public function addLogCongesInitiative(LogConges $logCongesInitiative): static
    {
        if (!$this->logCongesInitiative->contains($logCongesInitiative)) {
            $this->logCongesInitiative->add($logCongesInitiative);
            $logCongesInitiative->setUserInitiative($this);
        }

        return $this;
    }

    public function removeLogCongesInitiative(LogConges $logCongesInitiative): static
    {
        if ($this->logCongesInitiative->removeElement($logCongesInitiative)) {
            // set the owning side to null (unless already changed)
            if ($logCongesInitiative->getUserInitiative() === $this) {
                $logCongesInitiative->setUserInitiative(null);
            }
        }

        return $this;
    }


}
