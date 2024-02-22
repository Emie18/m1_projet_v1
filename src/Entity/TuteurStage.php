<?php

namespace App\Entity;

use App\Repository\TuteurStageRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TuteurStageRepository::class)]
class TuteurStage
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?int $num_tuteur_stage = null;

    #[ORM\Column(length: 255)]
    private ?string $nom = null;

    #[ORM\Column(length: 255)]
    private ?string $prenom = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNumTuteurStage(): ?int
    {
        return $this->num_tuteur_stage;
    }

    public function setNumTuteurStage(int $num_tuteur_stage): static
    {
        $this->num_tuteur_stage = $num_tuteur_stage;

        return $this;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(string $nom): static
    {
        $this->nom = $nom;

        return $this;
    }

    public function getPrenom(): ?string
    {
        return $this->prenom;
    }

    public function setPrenom(string $prenom): static
    {
        $this->prenom = $prenom;

        return $this;
    }
    public function __toString(): string
    {
        return $this->nom; // Remplacez "nom" par le nom de la propriété que vous souhaitez afficher
    }
}
