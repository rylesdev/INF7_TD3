<?php

namespace App\Entity;

use App\Repository\VisiteAnnonceRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: VisiteAnnonceRepository::class)]
#[ORM\HasLifecycleCallbacks]
class VisiteAnnonce
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 45, nullable: true)]
    private ?string $ipAddress = null;

    #[ORM\Column(type: 'datetime_immutable')]
    private ?\DateTimeImmutable $visiteLe = null;

    #[ORM\ManyToOne(targetEntity: Annonce::class, inversedBy: 'visites')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Annonce $annonce = null;

    #[ORM\PrePersist]
    public function onPrePersist(): void { $this->visiteLe = new \DateTimeImmutable(); }

    public function getId(): ?int { return $this->id; }
    public function getIpAddress(): ?string { return $this->ipAddress; }
    public function setIpAddress(?string $ipAddress): static { $this->ipAddress = $ipAddress; return $this; }
    public function getVisiteLe(): ?\DateTimeImmutable { return $this->visiteLe; }
    public function getAnnonce(): ?Annonce { return $this->annonce; }
    public function setAnnonce(?Annonce $annonce): static { $this->annonce = $annonce; return $this; }
}
