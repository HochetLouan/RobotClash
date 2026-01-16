<?php

namespace App\Entity;

use App\Repository\MatchsRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: MatchsRepository::class)]
#[ORM\Table(name: 'T_MATCH_MTC')]
class Matchs
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    #[ORM\Column(name: 'MTC_Id')]
    private ?int $id = null;

    #[ORM\Column(name: 'EQP_Id')]
    private ?int $equipeAId = null;

    #[ORM\Column(name: 'EQP_Id_EquipeB')]
    private ?int $equipeBId = null;

    #[ORM\Column(name: 'STM_Id')]
    private ?int $statusMatchId = 1;

    #[ORM\Column(name: 'CPT_Id')]
    private ?int $competitionId = null;

    #[ORM\Column(name: 'MTC_Horaire', type: 'datetime')]
    private ?\DateTimeInterface $horaire = null;

    #[ORM\Column(name: 'MTC_Commentaire', length: 32)]
    private ?string $commentaire = null;

    #[ORM\Column(name: 'MTC_Score', length: 32)]
    private ?string $score = null;

    #[ORM\Column(name: 'MTC_ForfaitEquipeA', type: 'integer', nullable: true)]
    private ?int $forfaitEquipeA = null;

    #[ORM\Column(name: 'MTC_ForfaitEquipeB', type: 'integer', nullable: true)]
    private ?int $forfaitEquipeB = null;

     #[ORM\Column(name: 'MTC_Terrain', type: 'integer')]
    private ?int $terrain = 1;


    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEquipeAId(): ?int
    {
        return $this->equipeAId;
    }

    public function setEquipeAId(int $equipeAId): self
    {
        $this->equipeAId = $equipeAId;
        return $this;
    }

    public function getEquipeBId(): ?int
    {
        return $this->equipeBId;
    }

    public function setEquipeBId(int $equipeBId): self
    {
        $this->equipeBId = $equipeBId;
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
    public function getHoraire(): ?\DateTimeInterface
    {
        return $this->horaire;
    }

    public function setHoraire(?\DateTimeInterface $horaire): self
    {
        $this->horaire = $horaire;
        return $this;
    }

    public function getStatusMatchId(): ?int
    {
        return $this->statusMatchId;
    }

    public function setStatusMatchId(int $statusMatchId): self
    {
        $this->statusMatchId = $statusMatchId;
        return $this;
    }

    public function getCommentaire(): ?string
    {
        return $this->commentaire;
    }

    public function setCommentaire(string $commentaire): self
    {
        $this->commentaire = $commentaire;
        return $this;
    }

    public function getScore(): ?string
    {
        return $this->score;
    }

    public function setScore(string $score): self
    {
        $this->score = $score;
        return $this;
    }

    public function getForfaitEquipeA(): ?int
    {
        return $this->forfaitEquipeA;
    }

    public function setForfaitEquipeA(?int $forfaitEquipeA): static
    {
        $this->forfaitEquipeA = $forfaitEquipeA;
        return $this;
    }

    public function getForfaitEquipeB(): ?int
    {
        return $this->forfaitEquipeB;
    }

    public function setForfaitEquipeB(?int $forfaitEquipeB): static
    {
        $this->forfaitEquipeB = $forfaitEquipeB;
        return $this;
    }

    public function getTerrain(): ?int
    {
        return $this->terrain;
    }

    public function setTerrain(?int $terrain): static
    {
        $this->terrain = $terrain;
        return $this;
    }
}
