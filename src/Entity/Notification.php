<?php

namespace App\Entity;

use App\Repository\NotificationRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;

#[ORM\Entity(repositoryClass: NotificationRepository::class)]
#[ORM\HasLifecycleCallbacks]
#[ApiResource(
    operations: [
        new GetCollection(security: "is_granted('ROLE_USER')"),
        new Get(security: "is_granted('ROLE_USER')"),
    ],
    normalizationContext: ['groups' => ['notification:read']],
    security: "is_granted('ROLE_USER')"
)]
class Notification
{
    public const TYPE_LOYER_RETARD = 'loyer_retard';
    public const TYPE_NOUVEAU_MESSAGE = 'nouveau_message';
    public const TYPE_TACHE = 'tache';
    public const TYPE_INFO = 'info';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    #[Groups(['notification:read'])]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 50)]
    #[Groups(['notification:read'])]
    private string $type = self::TYPE_INFO;

    #[ORM\Column(type: 'string', length: 255)]
    #[Groups(['notification:read'])]
    private ?string $titre = null;

    #[ORM\Column(type: 'text', nullable: true)]
    #[Groups(['notification:read'])]
    private ?string $message = null;

    #[ORM\Column(type: 'boolean')]
    #[Groups(['notification:read'])]
    private bool $lue = false;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $lien = null;

    #[ORM\Column(type: 'datetime_immutable')]
    #[Groups(['notification:read'])]
    private ?\DateTimeImmutable $creeLe = null;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'notifications')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    #[ORM\PrePersist]
    public function onPrePersist(): void { $this->creeLe = new \DateTimeImmutable(); }

    public function getId(): ?int { return $this->id; }
    public function getType(): string { return $this->type; }
    public function setType(string $type): static { $this->type = $type; return $this; }
    public function getTitre(): ?string { return $this->titre; }
    public function setTitre(string $titre): static { $this->titre = htmlspecialchars(strip_tags($titre), ENT_QUOTES, 'UTF-8'); return $this; }
    public function getMessage(): ?string { return $this->message; }
    public function setMessage(?string $message): static { $this->message = $message; return $this; }
    public function isLue(): bool { return $this->lue; }
    public function setLue(bool $lue): static { $this->lue = $lue; return $this; }
    public function getCreeLe(): ?\DateTimeImmutable { return $this->creeLe; }
    public function getUser(): ?User { return $this->user; }
    public function setUser(?User $user): static { $this->user = $user; return $this; }
    public function getLien(): ?string { return $this->lien; }
    public function setLien(?string $lien): static { $this->lien = $lien; return $this; }
}
