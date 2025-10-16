<?php
// src/Entity/User.php
namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;

#[ORM\Entity(repositoryClass: "App\Repository\UserRepository")]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "integer")]
    private ?int $id = null;

    #[ORM\Column(type: "string", length: 180, unique: true)]
    private ?string $email = null;

    #[ORM\Column(type: "string")]
    private ?string $password = null;

    // keep existing varchar role column for now
    #[ORM\Column(type: "string", length: 50)]
    private ?string $role = 'ROLE_USER';

    public function getId(): ?int { return $this->id; }

    public function getEmail(): ?string { return $this->email; }
    public function setEmail(string $email): self { $this->email = $email; return $this; }

    // required by Symfony - use email as unique identifier
    public function getUserIdentifier(): string { return (string) $this->email; }

    // match old code but return array (guarantee ROLE_USER)
    public function getRoles(): array
    {
        $roles = [];

        if ($this->role) {
            $roles[] = $this->role;
        }

        $roles[] = 'ROLE_USER';
        return array_values(array_unique($roles));
    }

    public function setRole(string $role): self { $this->role = $role; return $this; }

    public function getPassword(): ?string { return $this->password; }
    public function setPassword(string $password): self { $this->password = $password; return $this; }

    // required by the interface (nothing to erase)
    public function eraseCredentials(): void { }
}
