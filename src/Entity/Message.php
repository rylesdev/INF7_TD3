<?php

namespace App\Entity;

use App\Repository\MessageRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;

#[ORM\Entity(repositoryClass: MessageRepository::class)]
#[ORM\HasLifecycleCallbacks]
#[ApiResource(
    operations: [
        new GetCollection(security: "is_granted('ROLE_USER')"),
        new Get(security: "is_granted('ROLE_USER')"),
        new Post(security: "is_granted('ROLE_USER')"),
    ],
    normalizationContext: ['groups' => ['message:read']],
    denormalizationContext: ['groups' => ['message:write']]
)]
class Message
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    #[Groups(['message:read'])]
    private ?int $id = null;

    #[ORM\Column(type: 'text')]
    #[Assert\NotBlank(message: 'Le message ne peut pas être vide.')]
    #[Assert\Length(max: 5000)]
    #[Groups(['message:read', 'message:write'])]
    private ?string $contenu = null;

    #[ORM\Column(type: 'boolean')]
    #[Groups(['message:read'])]
    private bool $lu = false;

    #[ORM\Column(type: 'boolean')]
    private bool $automatique = false;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $lien = null;

    #[ORM\Column(type: 'datetime_immutable')]
    #[Groups(['message:read'])]
    private ?\DateTimeImmutable $envoyeLe = null;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'messagesEnvoyes')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['message:read'])]
    private ?User $expediteur = null;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'messagesRecus')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['message:read'])]
    private ?User $destinataire = null;

    #[ORM\ManyToOne(targetEntity: Colocation::class, inversedBy: 'messages')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['message:read'])]
    private ?Colocation $colocation = null;

    #[ORM\PrePersist]
    public function onPrePersist(): void { $this->envoyeLe = new \DateTimeImmutable(); }

    public function getId(): ?int { return $this->id; }
    public function getContenu(): ?string { return $this->contenu; }
    public function setContenu(string $contenu): static { $this->contenu = strip_tags($contenu); return $this; }
    public function isLu(): bool { return $this->lu; }
    public function setLu(bool $lu): static { $this->lu = $lu; return $this; }
    public function getEnvoyeLe(): ?\DateTimeImmutable { return $this->envoyeLe; }
    public function getExpediteur(): ?User { return $this->expediteur; }
    public function setExpediteur(?User $expediteur): static { $this->expediteur = $expediteur; return $this; }
    public function getDestinataire(): ?User { return $this->destinataire; }
    public function setDestinataire(?User $destinataire): static { $this->destinataire = $destinataire; return $this; }
    public function getColocation(): ?Colocation { return $this->colocation; }
    public function setColocation(?Colocation $colocation): static { $this->colocation = $colocation; return $this; }
    public function isAutomatique(): bool { return $this->automatique; }
    public function setAutomatique(bool $automatique): static { $this->automatique = $automatique; return $this; }
    public function getLien(): ?string { return $this->lien; }
    public function setLien(?string $lien): static { $this->lien = $lien; return $this; }
}
