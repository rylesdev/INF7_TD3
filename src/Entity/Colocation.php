<?php

namespace App\Entity;

use App\Repository\ColocationRepository;
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

#[ORM\Entity(repositoryClass: ColocationRepository::class)]
#[ORM\HasLifecycleCallbacks]
#[ApiResource(
    operations: [
        new GetCollection(),
        new Get(),
        new Post(security: "is_granted('ROLE_PROPRIETAIRE')"),
        new Put(security: "is_granted('ROLE_PROPRIETAIRE')"),
        new Delete(security: "is_granted('ROLE_PROPRIETAIRE')"),
    ],
    normalizationContext: ['groups' => ['colocation:read']],
    denormalizationContext: ['groups' => ['colocation:write']]
)]
class Colocation
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    #[Groups(['colocation:read'])]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 255)]
    #[Assert\NotBlank(message: 'Le nom est obligatoire.')]
    #[Assert\Length(max: 255)]
    #[Groups(['colocation:read', 'colocation:write'])]
    private ?string $nom = null;

    #[ORM\Column(type: 'string', length: 255)]
    #[Assert\NotBlank(message: "L'adresse est obligatoire.")]
    #[Groups(['colocation:read', 'colocation:write'])]
    private ?string $adresse = null;

    #[ORM\Column(type: 'string', length: 100)]
    #[Assert\NotBlank(message: 'La ville est obligatoire.')]
    #[Groups(['colocation:read', 'colocation:write'])]
    private ?string $ville = null;

    #[ORM\Column(type: 'string', length: 10)]
    #[Assert\NotBlank(message: 'Le code postal est obligatoire.')]
    #[Groups(['colocation:read', 'colocation:write'])]
    private ?string $codePostal = null;

    #[ORM\Column(type: 'text', nullable: true)]
    #[Groups(['colocation:read', 'colocation:write'])]
    private ?string $description = null;

    #[ORM\Column(type: 'decimal', precision: 10, scale: 2, nullable: true)]
    #[Assert\PositiveOrZero]
    #[Groups(['colocation:read', 'colocation:write'])]
    private ?string $loyer = null;

    #[ORM\Column(type: 'decimal', precision: 8, scale: 6, nullable: true)]
    #[Groups(['colocation:read', 'colocation:write'])]
    private ?string $latitude = null;

    #[ORM\Column(type: 'decimal', precision: 9, scale: 6, nullable: true)]
    #[Groups(['colocation:read', 'colocation:write'])]
    private ?string $longitude = null;

    #[ORM\Column(type: 'datetime_immutable')]
    #[Groups(['colocation:read'])]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'colocations')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['colocation:read'])]
    private ?User $proprietaire = null;

    #[ORM\OneToMany(mappedBy: 'colocation', targetEntity: Chambre::class, cascade: ['persist', 'remove'])]
    private Collection $chambres;

    #[ORM\OneToMany(mappedBy: 'colocation', targetEntity: Annonce::class, cascade: ['remove'])]
    private Collection $annonces;

    #[ORM\OneToMany(mappedBy: 'colocation', targetEntity: Loyer::class, cascade: ['remove'])]
    private Collection $loyers;

    #[ORM\OneToMany(mappedBy: 'colocation', targetEntity: Charge::class, cascade: ['remove'])]
    private Collection $charges;

    #[ORM\OneToMany(mappedBy: 'colocation', targetEntity: Message::class, cascade: ['remove'])]
    private Collection $messages;

    #[ORM\OneToMany(mappedBy: 'colocation', targetEntity: Tache::class, cascade: ['remove'])]
    private Collection $taches;

    public function __construct()
    {
        $this->chambres = new ArrayCollection();
        $this->annonces = new ArrayCollection();
        $this->loyers = new ArrayCollection();
        $this->charges = new ArrayCollection();
        $this->messages = new ArrayCollection();
        $this->taches = new ArrayCollection();
    }

    #[ORM\PrePersist]
    public function onPrePersist(): void
    {
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): ?int { return $this->id; }
    public function getNom(): ?string { return $this->nom; }
    public function setNom(string $nom): static { $this->nom = strip_tags($nom); return $this; }
    public function getAdresse(): ?string { return $this->adresse; }
    public function setAdresse(string $adresse): static { $this->adresse = strip_tags($adresse); return $this; }
    public function getVille(): ?string { return $this->ville; }
    public function setVille(string $ville): static { $this->ville = strip_tags($ville); return $this; }
    public function getCodePostal(): ?string { return $this->codePostal; }
    public function setCodePostal(string $codePostal): static { $this->codePostal = $codePostal; return $this; }
    public function getDescription(): ?string { return $this->description; }
    public function setDescription(?string $description): static { $this->description = $description; return $this; }
    public function getLoyer(): ?string { return $this->loyer; }
    public function setLoyer(?string $loyer): static { $this->loyer = $loyer; return $this; }
    public function getLatitude(): ?string { return $this->latitude; }
    public function setLatitude(?string $latitude): static { $this->latitude = $latitude; return $this; }
    public function getLongitude(): ?string { return $this->longitude; }
    public function setLongitude(?string $longitude): static { $this->longitude = $longitude; return $this; }
    public function getCreatedAt(): ?\DateTimeImmutable { return $this->createdAt; }
    public function getProprietaire(): ?User { return $this->proprietaire; }
    public function setProprietaire(?User $proprietaire): static { $this->proprietaire = $proprietaire; return $this; }
    public function getChambres(): Collection { return $this->chambres; }
    public function addChambre(Chambre $chambre): static { if (!$this->chambres->contains($chambre)) { $this->chambres->add($chambre); $chambre->setColocation($this); } return $this; }
    public function removeChambre(Chambre $chambre): static { if ($this->chambres->removeElement($chambre)) { if ($chambre->getColocation() === $this) { $chambre->setColocation(null); } } return $this; }
    public function getAnnonces(): Collection { return $this->annonces; }
    public function getLoyers(): Collection { return $this->loyers; }
    public function getCharges(): Collection { return $this->charges; }
    public function getMessages(): Collection { return $this->messages; }
    public function getTaches(): Collection { return $this->taches; }

    public function getSurfaceTotale(): float
    {
        $total = 0.0;
        foreach ($this->chambres as $chambre) {
            $total += (float) $chambre->getSurface();
        }
        return $total;
    }
}
