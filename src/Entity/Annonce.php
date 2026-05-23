<?php

namespace App\Entity;

use App\Repository\AnnonceRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;

#[ORM\Entity(repositoryClass: AnnonceRepository::class)]
#[ORM\HasLifecycleCallbacks]
#[ApiResource(
    operations: [
        new GetCollection(),
        new Get(),
        new Post(security: "is_granted('ROLE_PROPRIETAIRE')"),
        new Put(security: "is_granted('ROLE_PROPRIETAIRE')"),
        new Delete(security: "is_granted('ROLE_PROPRIETAIRE')"),
    ],
    normalizationContext: ['groups' => ['annonce:read']],
    denormalizationContext: ['groups' => ['annonce:write']]
)]
class Annonce
{
    public const STATUT_DISPONIBLE = 'disponible';
    public const STATUT_INDISPONIBLE = 'indisponible';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    #[Groups(['annonce:read'])]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 255)]
    #[Assert\NotBlank(message: 'Le titre est obligatoire.')]
    #[Assert\Length(max: 255)]
    #[Groups(['annonce:read', 'annonce:write'])]
    private ?string $titre = null;

    #[ORM\Column(type: 'text')]
    #[Assert\NotBlank(message: 'La description est obligatoire.')]
    #[Groups(['annonce:read', 'annonce:write'])]
    private ?string $description = null;

    #[ORM\Column(type: 'decimal', precision: 10, scale: 2)]
    #[Assert\NotNull]
    #[Assert\Positive(message: 'Le prix doit être positif.')]
    #[Groups(['annonce:read', 'annonce:write'])]
    private ?string $prix = null;

    #[ORM\Column(type: 'string', length: 255)]
    #[Assert\NotBlank]
    #[Groups(['annonce:read', 'annonce:write'])]
    private ?string $localisation = null;

    #[ORM\Column(type: 'string', length: 20)]
    #[Groups(['annonce:read', 'annonce:write'])]
    private string $statut = self::STATUT_DISPONIBLE;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    #[Groups(['annonce:read', 'annonce:write'])]
    private ?string $metaDescription = null;

    #[ORM\Column(type: 'datetime_immutable')]
    #[Groups(['annonce:read'])]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    #[Groups(['annonce:read'])]
    private ?\DateTimeImmutable $updatedAt = null;

    #[ORM\ManyToOne(targetEntity: Colocation::class, inversedBy: 'annonces')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['annonce:read'])]
    private ?Colocation $colocation = null;

    #[ORM\OneToMany(mappedBy: 'annonce', targetEntity: PhotoAnnonce::class, cascade: ['persist', 'remove'])]
    #[Groups(['annonce:read'])]
    private Collection $photos;

    #[ORM\OneToMany(mappedBy: 'annonce', targetEntity: VisiteAnnonce::class, cascade: ['remove'])]
    private Collection $visites;

    public function __construct()
    {
        $this->photos = new ArrayCollection();
        $this->visites = new ArrayCollection();
    }

    #[ORM\PrePersist]
    public function onPrePersist(): void { $this->createdAt = new \DateTimeImmutable(); }

    #[ORM\PreUpdate]
    public function onPreUpdate(): void { $this->updatedAt = new \DateTimeImmutable(); }

    public function getId(): ?int { return $this->id; }
    public function getTitre(): ?string { return $this->titre; }
    public function setTitre(string $titre): static { $this->titre = htmlspecialchars(strip_tags($titre), ENT_QUOTES, 'UTF-8'); return $this; }
    public function getDescription(): ?string { return $this->description; }
    public function setDescription(string $description): static { $this->description = $description; return $this; }
    public function getPrix(): ?string { return $this->prix; }
    public function setPrix(string $prix): static { $this->prix = $prix; return $this; }
    public function getLocalisation(): ?string { return $this->localisation; }
    public function setLocalisation(string $localisation): static { $this->localisation = htmlspecialchars(strip_tags($localisation), ENT_QUOTES, 'UTF-8'); return $this; }
    public function getStatut(): string { return $this->statut; }
    public function setStatut(string $statut): static { $this->statut = $statut; return $this; }
    public function isDisponible(): bool { return $this->statut === self::STATUT_DISPONIBLE; }
    public function getMetaDescription(): ?string { return $this->metaDescription; }
    public function setMetaDescription(?string $metaDescription): static { $this->metaDescription = $metaDescription; return $this; }
    public function getCreatedAt(): ?\DateTimeImmutable { return $this->createdAt; }
    public function getUpdatedAt(): ?\DateTimeImmutable { return $this->updatedAt; }
    public function getColocation(): ?Colocation { return $this->colocation; }
    public function setColocation(?Colocation $colocation): static { $this->colocation = $colocation; return $this; }
    public function getPhotos(): Collection { return $this->photos; }
    public function addPhoto(PhotoAnnonce $photo): static { if (!$this->photos->contains($photo)) { $this->photos->add($photo); $photo->setAnnonce($this); } return $this; }
    public function removePhoto(PhotoAnnonce $photo): static { if ($this->photos->removeElement($photo)) { if ($photo->getAnnonce() === $this) { $photo->setAnnonce(null); } } return $this; }
    public function getVisites(): Collection { return $this->visites; }
    public function getNbVisites(): int { return $this->visites->count(); }

    public function getPremierPhoto(): ?PhotoAnnonce
    {
        return $this->photos->first() ?: null;
    }
}
