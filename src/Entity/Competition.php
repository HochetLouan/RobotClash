<?php

namespace App\Entity;

use App\Repository\CompetitionRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CompetitionRepository::class)]
#[ORM\Table(name: 'T_COMPETITION_CPT')]
class Competition
{
    #[ORM\Column(name: 'CPT_Nom', length: 255)]
    private ?string $nom = null;

    #[ORM\Column(name: 'CPT_Lieu', length: 255, nullable: true)]
    private ?string $lieu = null;

    #[ORM\Column(name: 'CPT_DateDebut', type: Types::DATE_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $dateDebut = null;

    #[ORM\Column(name: 'CPT_DateFin', type: Types::DATE_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $dateFin = null;
    #[ORM\Column(name: 'CPT_Description', length: 255, nullable: true)]
    private ?string $description = null;

    #[ORM\Id]
    #[ORM\Column(name: 'CPT_Id')]
    #[ORM\GeneratedValue(strategy: "AUTO")]
    private ?int $id = null;

    #[ORM\Column(name: 'UTL_Id')]
    private ?int $idUTL = null;

    #[ORM\Column(name: 'CPT_NbMaxEquipesBracket', type: 'integer')]
    private int $nbEquipesMaxBracket = 0;

    #[ORM\Column(name: 'CPT_NbTerrain', type: 'integer')]
    private int $nbTerrain = 1;
    #[ORM\Column(name: 'CPT_PetiteFinale', type: 'boolean')]
    private bool $petiteFinale = false;

    #[ORM\Column(name: 'CPT_ArbreGenere', type: 'boolean')]
    private bool $arbreGenere = false;

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(string $nom): static
    {
        $this->nom = $nom;
        return $this;
    }

    public function getLieu(): ?string
    {
        return $this->lieu;
    }

    public function setLieu(?string $lieu): static
    {
        $this->lieu = $lieu;
        return $this;
    }

    public function getDateDebut(): ?\DateTimeInterface
    {
        return $this->dateDebut;
    }

    public function setDateDebut(?\DateTimeInterface $dateDebut): static
    {
        $this->dateDebut = $dateDebut;
        return $this;
    }

    public function getDateFin(): ?\DateTimeInterface
    {
        return $this->dateFin;
    }

    public function setDateFin(?\DateTimeInterface $dateFin): static
    {
        $this->dateFin = $dateFin;
        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function __tostring()
    {
        $debut = $this->dateDebut?->format('Y-m-d') ?? '';
        $fin = $this->dateFin?->format('Y-m-d') ?? '';

        return sprintf(
            '%s (%s - %s) at %s',
            $this->nom ?? '',
            $debut,
            $fin,
            $this->lieu ?? ''
        );
    }
    public function getDateDebutString(): string
    {
        return $this->dateDebut?->format('Y-m-d') ?? '';
    }
    public function getDateFinString(): string
    {
        return $this->dateFin?->format('Y-m-d') ?? '';
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getIdUTL(): ?int
    {
        return $this->idUTL;
    }

    public function setUTLId(int $idUTL): static
    {
        $this->idUTL = $idUTL;
        return $this;
    }

    public function getNbEquipesMaxBracket(): int
    {
        return $this->nbEquipesMaxBracket;
    }

    public function setNbEquipesMaxBracket(int $nbEquipesMaxBracket): self
    {
        $this->nbEquipesMaxBracket = $nbEquipesMaxBracket;
        return $this;
    }

    public function isPetiteFinale(): bool
    {
        return $this->petiteFinale;
    }
    public function setPetiteFinale(bool $petiteFinale): self
    {
        $this->petiteFinale = $petiteFinale;
        return $this;
    }

    public function isArbreGenere(): bool
    {
        return $this->arbreGenere;
    }

    public function setArbreGenere(bool $arbreGenere): self
    {
        $this->arbreGenere = $arbreGenere;
        return $this;
    }

    public function getNbTerrain(): ?int
    {
        return $this->nbTerrain;
    }

    public function setNbTerrain(?int $nbTerrain): static
    {
        $this->nbTerrain = $nbTerrain;

        return $this;
    }
}
