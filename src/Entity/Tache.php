<?php

namespace App\Entity;

use App\Repository\TacheRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;

#[ORM\Entity(repositoryClass: TacheRepository::class)]
#[ORM\HasLifecycleCallbacks]
#[ApiResource(
    operations: [
        new GetCollection(security: "is_granted('ROLE_USER')"),
        new Get(security: "is_granted('ROLE_USER')"),
        new Post(security: "is_granted('ROLE_USER')"),
        new Put(security: "is_granted('ROLE_USER')"),
        new Delete(security: "is_granted('ROLE_PROPRIETAIRE')"),
    ],
    normalizationContext: ['groups' => ['tache:read']],
    denormalizationContext: ['groups' => ['tache:write']]
)]
class Tache
{
    public const TYPE_VAISSELLE = 'vaisselle';
    public const TYPE_MENAGE = 'menage';
    public const TYPE_ENTRETIEN = 'entretien';
    public const TYPE_AUTRE = 'autre';

    public const STATUT_A_FAIRE = 'a_faire';
    public const STATUT_EN_COURS = 'en_cours';
    public const STATUT_TERMINEE = 'terminee';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    #[Groups(['tache:read'])]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 255)]
    #[Assert\NotBlank]
    #[Assert\Length(max: 255)]
    #[Groups(['tache:read', 'tache:write'])]
    private ?string $titre = null;

    #[ORM\Column(type: 'string', length: 50)]
    #[Assert\Choice(choices: [self::TYPE_VAISSELLE, self::TYPE_MENAGE, self::TYPE_ENTRETIEN, self::TYPE_AUTRE])]
    #[Groups(['tache:read', 'tache:write'])]
    private string $type = self::TYPE_AUTRE;

    #[ORM\Column(type: 'string', length: 20)]
    #[Groups(['tache:read', 'tache:write'])]
    private string $statut = self::STATUT_A_FAIRE;

    #[ORM\Column(type: 'date_immutable', nullable: true)]
    #[Groups(['tache:read', 'tache:write'])]
    private ?\DateTimeInterface $dateEcheance = null;

    #[ORM\Column(type: 'datetime_immutable')]
    #[Groups(['tache:read'])]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\ManyToOne(targetEntity: Colocation::class, inversedBy: 'taches')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['tache:read'])]
    private ?Colocation $colocation = null;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'taches')]
    #[Groups(['tache:read'])]
    private ?User $assigne = null;

    #[ORM\OneToOne(mappedBy: 'tache', targetEntity: Semainier::class, cascade: ['remove'])]
    private ?Semainier $semainier = null;

    #[ORM\PrePersist]
    public function onPrePersist(): void { $this->createdAt = new \DateTimeImmutable(); }

    public function getId(): ?int { return $this->id; }
    public function getTitre(): ?string { return $this->titre; }
    public function setTitre(string $titre): static { $this->titre = strip_tags($titre); return $this; }
    public function getType(): string { return $this->type; }
    public function setType(string $type): static { $this->type = $type; return $this; }
    public function getStatut(): string { return $this->statut; }
    public function setStatut(string $statut): static { $this->statut = $statut; return $this; }
    public function getDateEcheance(): ?\DateTimeInterface { return $this->dateEcheance; }
    public function setDateEcheance(?\DateTimeInterface $dateEcheance): static { $this->dateEcheance = $dateEcheance; return $this; }
    public function getCreatedAt(): ?\DateTimeImmutable { return $this->createdAt; }
    public function getColocation(): ?Colocation { return $this->colocation; }
    public function setColocation(?Colocation $colocation): static { $this->colocation = $colocation; return $this; }
    public function getAssigne(): ?User { return $this->assigne; }
    public function setAssigne(?User $assigne): static { $this->assigne = $assigne; return $this; }
    public function getSemainier(): ?Semainier { return $this->semainier; }

    public function getLibelleType(): string
    {
        return match ($this->type) {
            self::TYPE_VAISSELLE => 'Vaisselle',
            self::TYPE_MENAGE => 'Ménage',
            self::TYPE_ENTRETIEN => 'Entretien',
            default => 'Autre',
        };
    }
}
