<?php

namespace App\Entity;

use App\Repository\SemainierRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SemainierRepository::class)]
#[ORM\HasLifecycleCallbacks]
class Semainier
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(type: 'integer')]
    private int $jourSemaine = 1;

    #[ORM\Column(type: 'date_immutable')]
    private ?\DateTimeInterface $dateDebut = null;

    #[ORM\Column(type: 'date_immutable')]
    private ?\DateTimeInterface $dateFin = null;

    #[ORM\Column(type: 'datetime_immutable')]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\OneToOne(inversedBy: 'semainier', targetEntity: Tache::class)]
    #[ORM\JoinColumn(nullable: false)]
    private ?Tache $tache = null;

    #[ORM\PrePersist]
    public function onPrePersist(): void { $this->createdAt = new \DateTimeImmutable(); }

    public function getId(): ?int { return $this->id; }
    public function getJourSemaine(): int { return $this->jourSemaine; }
    public function setJourSemaine(int $jourSemaine): static { $this->jourSemaine = $jourSemaine; return $this; }
    public function getDateDebut(): ?\DateTimeInterface { return $this->dateDebut; }
    public function setDateDebut(\DateTimeInterface $dateDebut): static { $this->dateDebut = $dateDebut; return $this; }
    public function getDateFin(): ?\DateTimeInterface { return $this->dateFin; }
    public function setDateFin(\DateTimeInterface $dateFin): static { $this->dateFin = $dateFin; return $this; }
    public function getCreatedAt(): ?\DateTimeImmutable { return $this->createdAt; }
    public function getTache(): ?Tache { return $this->tache; }
    public function setTache(?Tache $tache): static { $this->tache = $tache; return $this; }

    public function getLibelleJour(): string
    {
        $jours = ['Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi', 'Dimanche'];
        return $jours[($this->jourSemaine - 1) % 7] ?? 'Lundi';
    }
}
