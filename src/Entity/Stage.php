<?php

namespace App\Entity;

use App\Repository\StageRepository;
use Doctrine\DBAL\Types\Types;
use App\Entity\Groupe;
use App\Entity\Etat;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: StageRepository::class)]
class Stage
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $titre = null;

    #[ORM\Column(length: 1000)]
    private ?string $description = null;

    #[ORM\Column(length: 1000, nullable: true)]
    private ?string $commentaire = null;

    #[ORM\ManyToOne(targetEntity: TuteurIsen::class)]
    private ?TuteurIsen $tuteur_isen = null;

    #[ORM\ManyToOne(targetEntity: TuteurStage::class)]
    private ?TuteurStage $tuteur_stage = null;

    #[ORM\ManyToOne(targetEntity: Apprenant::class)]
    private ?Apprenant $apprenant = null;

    #[ORM\ManyToOne(targetEntity: Entreprise::class)]
    private ?Entreprise $entreprise = null;

    #[ORM\ManyToOne(targetEntity: Groupe::class)]
    private ?Groupe $groupe = null;

    #[ORM\ManyToOne(targetEntity: Etat::class)]
    private ?Etat $soutenance = null;

    #[ORM\ManyToOne(targetEntity: Etat::class)]
    private ?Etat $eval_entreprise = null;

    #[ORM\ManyToOne(targetEntity: Etat::class)]
    private ?Etat $rapport = null;

    #[ORM\Column]
    private ?int $num_stage = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $date_soutenance = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $date_debut = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $date_fin = null;

    #[ORM\Column(nullable: true)]
    private ?bool $visio = null;

    #[ORM\Column(nullable: true)]
    private ?bool $rapport_remis = null;

    #[ORM\Column(nullable: true)]
    private ?bool $confidentiel = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitre(): ?string
    {
        return $this->titre;
    }

    public function setTitre(string $titre): static
    {
        $this->titre = $titre;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getCommentaire(): ?string
    {
        return $this->commentaire;
    }

    public function setCommentaire(?string $commentaire): static
    {
        $this->commentaire = $commentaire;

        return $this;
    }

    public function getTuteurIsen(): ?TuteurIsen
    {
        return $this->tuteur_isen;
    }

    public function setTuteurIsen(?TuteurIsen $tuteur_isen): static
    {
        $this->tuteur_isen = $tuteur_isen;

        return $this;
    }

    public function getTuteurStage(): ?TuteurStage
    {
        return $this->tuteur_stage;
    }

    public function setTuteurStage(?TuteurStage $tuteur_stage): static
    {
        $this->tuteur_stage = $tuteur_stage;

        return $this;
    }

    public function getApprenant(): ?Apprenant
    {
        return $this->apprenant;
    }

    public function setApprenant(?Apprenant $apprenant): static
    {
        $this->apprenant = $apprenant;

        return $this;
    }

    public function getEntreprise(): ?entreprise
    {
        return $this->entreprise;
    }

    public function setEntreprise(?entreprise $entreprise): static
    {
        $this->entreprise = $entreprise;

        return $this;
    }

    public function getGroupe(): ?groupe
    {
        return $this->groupe;
    }

    public function setGroupe(?groupe $groupe): static
    {
        $this->groupe = $groupe;

        return $this;
    }

    public function getSoutenance(): ?etat
    {
        return $this->soutenance;
    }

    public function setSoutenance(?etat $soutenance): static
    {
        $this->soutenance = $soutenance;

        return $this;
    }

    public function getEvalEntreprise(): ?etat
    {
        return $this->eval_entreprise;
    }

    public function setEvalEntreprise(?etat $eval_entreprise): static
    {
        $this->eval_entreprise = $eval_entreprise;

        return $this;
    }

    public function getRapport(): ?etat
    {
        return $this->rapport;
    }

    public function setRapport(?etat $rapport): static
    {
        $this->rapport = $rapport;

        return $this;
    }

    public function getNumStage(): ?int
    {
        return $this->num_stage;
    }

    public function setNumStage(int $num_stage): static
    {
        $this->num_stage = $num_stage;

        return $this;
    }

    public function getDateSoutenance(): ?\DateTimeInterface
    {
        return $this->date_soutenance;
    }

    public function setDateSoutenance(?\DateTimeInterface $date_soutenance): static
    {
        $this->date_soutenance = $date_soutenance;

        return $this;
    }

    public function getDateDebut(): ?\DateTimeInterface
    {
        return $this->date_debut;
    }

    public function setDateDebut(\DateTimeInterface $date_debut): static
    {
        $this->date_debut = $date_debut;

        return $this;
    }

    public function getDateFin(): ?\DateTimeInterface
    {
        return $this->date_fin;
    }

    public function setDateFin(\DateTimeInterface $date_fin): static
    {
        $this->date_fin = $date_fin;

        return $this;
    }
    public function isVisio(): ?bool
    {
        return $this->visio;
    }

    public function setVisio(bool $visio_confi): static
    {
        $this->visio = $visio_confi;

        return $this;
    }

    public function isRapportRemis(): ?bool
    {
        return $this->rapport_remis;
    }

    public function setRapportRemis(bool $rapport_remis): static
    {
        $this->rapport_remis = $rapport_remis;

        return $this;
    }

    public function isConfidentiel(): ?bool
    {
        return $this->confidentiel;
    }

    public function setConfidentiel(?bool $confidentiel): static
    {
        $this->confidentiel = $confidentiel;

        return $this;
    }
}