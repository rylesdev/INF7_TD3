<?php

namespace App\Entity;

use App\Repository\LoyerRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;

#[ORM\Entity(repositoryClass: LoyerRepository::class)]
#[ORM\HasLifecycleCallbacks]
#[ApiResource(
    operations: [
        new GetCollection(security: "is_granted('ROLE_USER')"),
        new Get(security: "is_granted('ROLE_USER')"),
        new Post(security: "is_granted('ROLE_PROPRIETAIRE')"),
        new Put(security: "is_granted('ROLE_PROPRIETAIRE')"),
    ],
    normalizationContext: ['groups' => ['loyer:read']],
    denormalizationContext: ['groups' => ['loyer:write']]
)]
class Loyer
{
    public const STATUT_PAYE = 'payé';
    public const STATUT_IMPAYE = 'impayé';
    public const STATUT_EN_RETARD = 'en_retard';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    #[Groups(['loyer:read'])]
    private ?int $id = null;

    #[ORM\Column(type: 'decimal', precision: 10, scale: 2)]
    #[Assert\NotNull]
    #[Assert\Positive(message: 'Le montant doit être positif.')]
    #[Groups(['loyer:read', 'loyer:write'])]
    private ?string $montant = null;

    #[ORM\Column(type: 'date_immutable')]
    #[Assert\NotNull]
    #[Groups(['loyer:read', 'loyer:write'])]
    private ?\DateTimeInterface $dateEcheance = null;

    #[ORM\Column(type: 'date_immutable', nullable: true)]
    #[Groups(['loyer:read', 'loyer:write'])]
    private ?\DateTimeInterface $datePaiement = null;

    #[ORM\Column(type: 'string', length: 20)]
    #[Groups(['loyer:read', 'loyer:write'])]
    private string $statut = self::STATUT_IMPAYE;

    #[ORM\Column(type: 'string', length: 20)]
    #[Groups(['loyer:read', 'loyer:write'])]
    private ?string $mois = null;

    #[ORM\Column(type: 'integer')]
    #[Groups(['loyer:read', 'loyer:write'])]
    private ?int $annee = null;

    #[ORM\Column(type: 'datetime_immutable')]
    #[Groups(['loyer:read'])]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\ManyToOne(targetEntity: Colocation::class, inversedBy: 'loyers')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['loyer:read'])]
    private ?Colocation $colocation = null;

    #[ORM\ManyToOne(targetEntity: Chambre::class, inversedBy: 'loyers')]
    #[Groups(['loyer:read'])]
    private ?Chambre $chambre = null;

    #[ORM\OneToOne(mappedBy: 'loyer', targetEntity: Quittance::class, cascade: ['remove'])]
    private ?Quittance $quittance = null;

    #[ORM\PrePersist]
    public function onPrePersist(): void { $this->createdAt = new \DateTimeImmutable(); }

    public function getId(): ?int { return $this->id; }
    public function getMontant(): ?string { return $this->montant; }
    public function setMontant(string $montant): static { $this->montant = $montant; return $this; }
    public function getDateEcheance(): ?\DateTimeInterface { return $this->dateEcheance; }
    public function setDateEcheance(\DateTimeInterface $dateEcheance): static { $this->dateEcheance = $dateEcheance; return $this; }
    public function getDatePaiement(): ?\DateTimeInterface { return $this->datePaiement; }
    public function setDatePaiement(?\DateTimeInterface $datePaiement): static { $this->datePaiement = $datePaiement; return $this; }
    public function getStatut(): string { return $this->statut; }
    public function setStatut(string $statut): static { $this->statut = $statut; return $this; }
    public function getMois(): ?string { return $this->mois; }
    public function setMois(?string $mois): static { $this->mois = $mois; return $this; }
    public function getAnnee(): ?int { return $this->annee; }
    public function setAnnee(?int $annee): static { $this->annee = $annee; return $this; }
    public function getCreatedAt(): ?\DateTimeImmutable { return $this->createdAt; }
    public function getColocation(): ?Colocation { return $this->colocation; }
    public function setColocation(?Colocation $colocation): static { $this->colocation = $colocation; return $this; }
    public function getChambre(): ?Chambre { return $this->chambre; }
    public function setChambre(?Chambre $chambre): static { $this->chambre = $chambre; return $this; }
    public function getQuittance(): ?Quittance { return $this->quittance; }
    public function isPaye(): bool { return $this->statut === self::STATUT_PAYE; }
}
