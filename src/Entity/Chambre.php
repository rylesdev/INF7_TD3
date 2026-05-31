<?php

namespace App\Entity;

use App\Repository\ChambreRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use ApiPlatform\Metadata\Delete;

#[ORM\Entity(repositoryClass: ChambreRepository::class)]
#[ApiResource(
    operations: [
        new GetCollection(security: "is_granted('ROLE_USER')"),
        new Get(security: "is_granted('ROLE_USER')"),
        new Post(security: "is_granted('ROLE_PROPRIETAIRE')"),
        new Put(security: "is_granted('ROLE_PROPRIETAIRE')"),
        new Delete(security: "is_granted('ROLE_PROPRIETAIRE')"),
    ],
    normalizationContext: ['groups' => ['chambre:read']],
    denormalizationContext: ['groups' => ['chambre:write']]
)]
class Chambre
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    #[Groups(['chambre:read'])]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 100)]
    #[Assert\NotBlank]
    #[Groups(['chambre:read', 'chambre:write'])]
    private ?string $nom = null;

    #[ORM\Column(type: 'decimal', precision: 6, scale: 2)]
    #[Assert\NotNull]
    #[Assert\Positive(message: 'La surface doit être positive.')]
    #[Groups(['chambre:read', 'chambre:write'])]
    private ?string $surface = null;

    #[ORM\Column(type: 'decimal', precision: 10, scale: 2, nullable: true)]
    #[Assert\PositiveOrZero]
    #[Groups(['chambre:read', 'chambre:write'])]
    private ?string $loyerMensuel = null;

    #[ORM\ManyToOne(targetEntity: Colocation::class, inversedBy: 'chambres')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['chambre:read'])]
    private ?Colocation $colocation = null;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'chambres')]
    #[Groups(['chambre:read'])]
    private ?User $locataire = null;

    #[ORM\OneToMany(mappedBy: 'chambre', targetEntity: Tantieme::class, cascade: ['remove'])]
    private Collection $tantiemes;

    #[ORM\OneToMany(mappedBy: 'chambre', targetEntity: Loyer::class, cascade: ['remove'])]
    private Collection $loyers;

    public function __construct()
    {
        $this->tantiemes = new ArrayCollection();
        $this->loyers = new ArrayCollection();
    }

    public function getId(): ?int { return $this->id; }
    public function getNom(): ?string { return $this->nom; }
    public function setNom(string $nom): static { $this->nom = strip_tags($nom); return $this; }
    public function getSurface(): ?string { return $this->surface; }
    public function setSurface(string $surface): static { $this->surface = $surface; return $this; }
    public function getLoyerMensuel(): ?string { return $this->loyerMensuel; }
    public function setLoyerMensuel(?string $loyerMensuel): static { $this->loyerMensuel = $loyerMensuel; return $this; }
    public function getColocation(): ?Colocation { return $this->colocation; }
    public function setColocation(?Colocation $colocation): static { $this->colocation = $colocation; return $this; }
    public function getLocataire(): ?User { return $this->locataire; }
    public function setLocataire(?User $locataire): static { $this->locataire = $locataire; return $this; }
    public function getTantiemes(): Collection { return $this->tantiemes; }
    public function getLoyers(): Collection { return $this->loyers; }

    public function getPourcentageSurface(): float
    {
        if (!$this->colocation) return 0.0;
        $total = $this->colocation->getSurfaceTotale();
        if ($total <= 0) return 0.0;
        return round(((float) $this->surface / $total) * 100, 2);
    }
}
