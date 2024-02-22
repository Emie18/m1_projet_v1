<?php

namespace App\Entity;

use App\Repository\TuteurIsenRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TuteurIsenRepository::class)]
class TuteurIsen
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?int $num_tuteur_isen = null;

    #[ORM\Column(length: 255)]
    private ?string $nom = null;

    #[ORM\Column(length: 255)]
    private ?string $prenom = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNumTuteurIsen(): ?int
    {
        return $this->num_tuteur_isen;
    }

    public function setNumTuteurIsen(int $num_tuteur_isen): static
    {
        $this->num_tuteur_isen = $num_tuteur_isen;

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
