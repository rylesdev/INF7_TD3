<?php

namespace App\Entity;

use App\Repository\TantiemeRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TantiemeRepository::class)]
#[ORM\HasLifecycleCallbacks]
class Tantieme
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(type: 'decimal', precision: 5, scale: 2)]
    private ?string $pourcentage = null;

    #[ORM\Column(type: 'decimal', precision: 10, scale: 2)]
    private ?string $montantDu = null;

    #[ORM\Column(type: 'datetime_immutable')]
    private ?\DateTimeImmutable $calculeLe = null;

    #[ORM\ManyToOne(targetEntity: Chambre::class, inversedBy: 'tantiemes')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Chambre $chambre = null;

    #[ORM\ManyToOne(targetEntity: Charge::class, inversedBy: 'tantiemes')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Charge $charge = null;

    #[ORM\PrePersist]
    public function onPrePersist(): void { $this->calculeLe = new \DateTimeImmutable(); }

    public function getId(): ?int { return $this->id; }
    public function getPourcentage(): ?string { return $this->pourcentage; }
    public function setPourcentage(string $pourcentage): static { $this->pourcentage = $pourcentage; return $this; }
    public function getMontantDu(): ?string { return $this->montantDu; }
    public function setMontantDu(string $montantDu): static { $this->montantDu = $montantDu; return $this; }
    public function getCalculeLe(): ?\DateTimeImmutable { return $this->calculeLe; }
    public function getChambre(): ?Chambre { return $this->chambre; }
    public function setChambre(?Chambre $chambre): static { $this->chambre = $chambre; return $this; }
    public function getCharge(): ?Charge { return $this->charge; }
    public function setCharge(?Charge $charge): static { $this->charge = $charge; return $this; }
}
