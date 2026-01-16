<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: 'T_UTILISATEUR_UTL')]
#[ORM\UniqueConstraint(name: 'UNIQ_IDENTIFIER_EMAIL', fields: ['email'])]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Column(name: 'UTL_Nom', length: 255)]
    private ?string $nom = null;


    #[ORM\Column(name: 'UTL_Prenom', length: 255)]
    private ?string $prenom = null;


    #[ORM\Column(name: 'UTL_Mail', length: 180)]
    private ?string $email = null;


    #[ORM\Column(name: 'UTL_MotDePasse')]
    private ?string $mot_de_passe = null;


    #[ORM\Column(name: 'ROL_Id')]
    private int $role = 1;

    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    #[ORM\Column(name: 'UTL_Id')]
    private ?int $id = null;

    #[ORM\Column(name: 'UTL_Langue')]
    private ?string $langue = 'fr';


    public function getId(): ?int
    {
        return $this->id;
    }


    public function getNom(): ?string
    {
        return $this->nom;
    }


    public function setNom(string $nom): self
    {
        $this->nom = $nom;
        return $this;
    }


    public function getPrenom(): ?string
    {
        return $this->prenom;
    }


    public function setPrenom(string $prenom): self
    {
        $this->prenom = $prenom;
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


    public function getUserIdentifier(): string
    {
        return (string) $this->id;
    }

    public function getRole(): ?int
    {
        return $this->role;
    }

    public function setRole(int $role): self
    {
        $this->role = $role;
        return $this;
    }


    public function getRoles(): array
    {
        return match ($this->role) {
            1 => ['ROLE_ORGANISATEUR'],
            default => ['ROLE_USER'],
        };
    }

    public function getRolesTexte(): string
    {
        return match ($this->role) {
            1 => 'Organisateur',
            default => 'Utilisateur',
        };
    }

    public function getPassword(): ?string
    {
        return $this->mot_de_passe;
    }


    public function setMotDePasse(string $mot_de_passe): static
    {
        $this->mot_de_passe = $mot_de_passe;


        return $this;
    }

    public function getLangue(): ?string
    {
        return $this->langue;
    }

    public function setLangue(string $langue): self
    {
        $this->langue = $langue;
        return $this;
    }


    public function __serialize(): array
    {
        $data = (array) $this;
        $data["\0" . self::class . "\0mot_de_passe"] = hash('crc32c', $this->mot_de_passe);


        return $data;
    }


    #[\Deprecated]
    public function eraseCredentials(): void
    {
    }
}
