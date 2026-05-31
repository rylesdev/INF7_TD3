<?php

namespace App\Entity;

use App\Repository\CandidatureRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CandidatureRepository::class)]
#[ORM\HasLifecycleCallbacks]
class Candidature
{
    const STATUT_EN_ATTENTE = 'en_attente';
    const STATUT_ACCEPTE    = 'accepte';
    const STATUT_REFUSE     = 'refuse';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $locataire = null;

    #[ORM\ManyToOne(targetEntity: Annonce::class)]
    #[ORM\JoinColumn(nullable: false)]
    private ?Annonce $annonce = null;

    #[ORM\Column(type: 'string', length: 20)]
    private string $statut = self::STATUT_EN_ATTENTE;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $pieceIdentite = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $justificatifRevenu = null;

    #[ORM\Column(type: 'datetime_immutable')]
    private ?\DateTimeImmutable $creeLe = null;

    #[ORM\PrePersist]
    public function onPrePersist(): void { $this->creeLe = new \DateTimeImmutable(); }

    public function getId(): ?int { return $this->id; }
    public function getLocataire(): ?User { return $this->locataire; }
    public function setLocataire(?User $locataire): static { $this->locataire = $locataire; return $this; }
    public function getAnnonce(): ?Annonce { return $this->annonce; }
    public function setAnnonce(?Annonce $annonce): static { $this->annonce = $annonce; return $this; }
    public function getStatut(): string { return $this->statut; }
    public function setStatut(string $statut): static { $this->statut = $statut; return $this; }
    public function getCreeLe(): ?\DateTimeImmutable { return $this->creeLe; }
    public function isEnAttente(): bool { return $this->statut === self::STATUT_EN_ATTENTE; }
    public function isAccepte(): bool { return $this->statut === self::STATUT_ACCEPTE; }
    public function isRefuse(): bool { return $this->statut === self::STATUT_REFUSE; }
    public function getPieceIdentite(): ?string { return $this->pieceIdentite; }
    public function setPieceIdentite(?string $v): static { $this->pieceIdentite = $v; return $this; }
    public function getJustificatifRevenu(): ?string { return $this->justificatifRevenu; }
    public function setJustificatifRevenu(?string $v): static { $this->justificatifRevenu = $v; return $this; }
}
