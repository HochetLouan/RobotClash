<?php

namespace App\Entity;

use App\Repository\EquipeRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: EquipeRepository::class)]
#[ORM\Table(name: 'T_EQUIPE_EQP')]
class Equipe
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    #[ORM\Column(name: 'EQP_Id')]
    private ?int $id = null;

    #[ORM\Column(name: 'SEQ_Id')]
    private ?int $seqId = null;

    #[ORM\Column(name: 'CPT_Id')]
    private ?int $competitionId = null;

    #[ORM\Column(name: 'UTL_Id')]
    private ?int $userId = null;

    #[ORM\Column(name: 'EQP_Nom', length: 32)]
    private ?string $nom = null;

    #[ORM\Column(name: 'EQP_DateCreation', type: 'datetime')]
    private ?\DateTimeInterface $dateCreation = null;

    #[ORM\Column(name: 'EQP_Etablissement', length: 32)]
    private ?string $etablissement = null;

    #[ORM\Column(name: 'EQP_Membres', nullable: true)]
    private ?string $membres = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSeqId(): ?int
    {
        return $this->seqId;
    }

    public function setSeqId(int $seqId): self
    {
        $this->seqId = $seqId;
        return $this;
    }

    public function getCompetitionId(): ?int
    {
        return $this->competitionId;
    }

    public function setCompetitionId(int $competitionId): self
    {
        $this->competitionId = $competitionId;
        return $this;
    }

    public function getUserId(): ?int
    {
        return $this->userId;
    }

    public function setUserId(int $userId): self
    {
        $this->userId = $userId;
        return $this;
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

    public function getDateCreation(): ?\DateTimeInterface
    {
        return $this->dateCreation;
    }

    public function setDateCreation(\DateTimeInterface $dateCreation): self
    {
        $this->dateCreation = $dateCreation;
        return $this;
    }

    public function getEtablissement(): ?string
    {
        return $this->etablissement;
    }

    public function setEtablissement(string $etablissement): self
    {
        $this->etablissement = $etablissement;
        return $this;
    }

    public function getMembres(): ?string
    {
        return $this->membres;
    }

    public function setMembres(?string $membres): self
    {
        $this->membres = $membres;
        return $this;
    }
}
