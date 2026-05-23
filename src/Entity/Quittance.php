<?php

namespace App\Entity;

use App\Repository\QuittanceRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: QuittanceRepository::class)]
#[ORM\HasLifecycleCallbacks]
class Quittance
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(type: 'decimal', precision: 10, scale: 2)]
    #[Assert\NotNull]
    #[Assert\Positive]
    private ?string $montantLoyer = null;

    #[ORM\Column(type: 'decimal', precision: 10, scale: 2)]
    private string $montantCharges = '0.00';

    #[ORM\Column(type: 'decimal', precision: 10, scale: 2)]
    private ?string $montantTotal = null;

    #[ORM\Column(type: 'date_immutable')]
    private ?\DateTimeInterface $periodeDebut = null;

    #[ORM\Column(type: 'date_immutable')]
    private ?\DateTimeInterface $periodeFin = null;

    #[ORM\Column(type: 'datetime_immutable')]
    private ?\DateTimeImmutable $genereeAt = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $pdfPath = null;

    #[ORM\OneToOne(inversedBy: 'quittance', targetEntity: Loyer::class)]
    #[ORM\JoinColumn(nullable: false)]
    private ?Loyer $loyer = null;

    #[ORM\PrePersist]
    public function onPrePersist(): void { $this->genereeAt = new \DateTimeImmutable(); }

    public function getId(): ?int { return $this->id; }
    public function getMontantLoyer(): ?string { return $this->montantLoyer; }
    public function setMontantLoyer(string $montantLoyer): static { $this->montantLoyer = $montantLoyer; return $this; }
    public function getMontantCharges(): string { return $this->montantCharges; }
    public function setMontantCharges(string $montantCharges): static { $this->montantCharges = $montantCharges; return $this; }
    public function getMontantTotal(): ?string { return $this->montantTotal; }
    public function setMontantTotal(string $montantTotal): static { $this->montantTotal = $montantTotal; return $this; }
    public function getPeriodeDebut(): ?\DateTimeInterface { return $this->periodeDebut; }
    public function setPeriodeDebut(\DateTimeInterface $periodeDebut): static { $this->periodeDebut = $periodeDebut; return $this; }
    public function getPeriodeFin(): ?\DateTimeInterface { return $this->periodeFin; }
    public function setPeriodeFin(\DateTimeInterface $periodeFin): static { $this->periodeFin = $periodeFin; return $this; }
    public function getGenereeAt(): ?\DateTimeImmutable { return $this->genereeAt; }
    public function getPdfPath(): ?string { return $this->pdfPath; }
    public function setPdfPath(?string $pdfPath): static { $this->pdfPath = $pdfPath; return $this; }
    public function getLoyer(): ?Loyer { return $this->loyer; }
    public function setLoyer(?Loyer $loyer): static { $this->loyer = $loyer; return $this; }
}
