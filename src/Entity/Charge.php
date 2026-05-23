<?php

namespace App\Entity;

use App\Repository\ChargeRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use ApiPlatform\Metadata\Delete;

#[ORM\Entity(repositoryClass: ChargeRepository::class)]
#[ORM\HasLifecycleCallbacks]
#[ApiResource(
    operations: [
        new GetCollection(security: "is_granted('ROLE_USER')"),
        new Get(security: "is_granted('ROLE_USER')"),
        new Post(security: "is_granted('ROLE_PROPRIETAIRE')"),
        new Put(security: "is_granted('ROLE_PROPRIETAIRE')"),
        new Delete(security: "is_granted('ROLE_PROPRIETAIRE')"),
    ],
    normalizationContext: ['groups' => ['charge:read']],
    denormalizationContext: ['groups' => ['charge:write']]
)]
class Charge
{
    public const TYPE_EAU = 'eau';
    public const TYPE_ELECTRICITE = 'electricite';
    public const TYPE_INTERNET = 'internet';
    public const TYPE_TAXES = 'taxes';
    public const TYPE_AUTRE = 'autre';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    #[Groups(['charge:read'])]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 50)]
    #[Assert\NotBlank]
    #[Assert\Choice(choices: [self::TYPE_EAU, self::TYPE_ELECTRICITE, self::TYPE_INTERNET, self::TYPE_TAXES, self::TYPE_AUTRE])]
    #[Groups(['charge:read', 'charge:write'])]
    private ?string $type = null;

    #[ORM\Column(type: 'decimal', precision: 10, scale: 2)]
    #[Assert\NotNull]
    #[Assert\Positive(message: 'Le montant doit être positif.')]
    #[Groups(['charge:read', 'charge:write'])]
    private ?string $montant = null;

    #[ORM\Column(type: 'date_immutable')]
    #[Assert\NotNull]
    #[Groups(['charge:read', 'charge:write'])]
    private ?\DateTimeInterface $date = null;

    #[ORM\Column(type: 'string', length: 20, nullable: true)]
    #[Groups(['charge:read', 'charge:write'])]
    private ?string $mois = null;

    #[ORM\Column(type: 'integer', nullable: true)]
    #[Groups(['charge:read', 'charge:write'])]
    private ?int $annee = null;

    #[ORM\Column(type: 'text', nullable: true)]
    #[Groups(['charge:read', 'charge:write'])]
    private ?string $description = null;

    #[ORM\Column(type: 'datetime_immutable')]
    #[Groups(['charge:read'])]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\ManyToOne(targetEntity: Colocation::class, inversedBy: 'charges')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['charge:read'])]
    private ?Colocation $colocation = null;

    #[ORM\OneToMany(mappedBy: 'charge', targetEntity: Tantieme::class, cascade: ['persist', 'remove'])]
    private \Doctrine\Common\Collections\Collection $tantiemes;

    public function __construct()
    {
        $this->tantiemes = new \Doctrine\Common\Collections\ArrayCollection();
    }

    #[ORM\PrePersist]
    public function onPrePersist(): void { $this->createdAt = new \DateTimeImmutable(); }

    public function getId(): ?int { return $this->id; }
    public function getType(): ?string { return $this->type; }
    public function setType(string $type): static { $this->type = $type; return $this; }
    public function getMontant(): ?string { return $this->montant; }
    public function setMontant(string $montant): static { $this->montant = $montant; return $this; }
    public function getDate(): ?\DateTimeInterface { return $this->date; }
    public function setDate(\DateTimeInterface $date): static { $this->date = $date; return $this; }
    public function getMois(): ?string { return $this->mois; }
    public function setMois(?string $mois): static { $this->mois = $mois; return $this; }
    public function getAnnee(): ?int { return $this->annee; }
    public function setAnnee(?int $annee): static { $this->annee = $annee; return $this; }
    public function getDescription(): ?string { return $this->description; }
    public function setDescription(?string $description): static { $this->description = $description; return $this; }
    public function getCreatedAt(): ?\DateTimeImmutable { return $this->createdAt; }
    public function getColocation(): ?Colocation { return $this->colocation; }
    public function setColocation(?Colocation $colocation): static { $this->colocation = $colocation; return $this; }
    public function getTantiemes(): \Doctrine\Common\Collections\Collection { return $this->tantiemes; }

    public function getLibelleType(): string
    {
        return match ($this->type) {
            self::TYPE_EAU => 'Eau',
            self::TYPE_ELECTRICITE => 'Électricité',
            self::TYPE_INTERNET => 'Internet',
            self::TYPE_TAXES => 'Taxes',
            default => 'Autre',
        };
    }
}
