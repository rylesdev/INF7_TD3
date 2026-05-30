<?php

namespace App\Entity;

use App\Repository\AvisAnnonceRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: AvisAnnonceRepository::class)]
#[ORM\HasLifecycleCallbacks]
class AvisAnnonce
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(type: 'integer')]
    #[Assert\Range(min: 1, max: 5)]
    private int $note = 5;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $commentaire = null;

    #[ORM\Column(type: 'datetime_immutable')]
    private ?\DateTimeImmutable $creeLe = null;

    #[ORM\ManyToOne(targetEntity: Annonce::class, inversedBy: 'avis')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Annonce $annonce = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $auteur = null;

    #[ORM\PrePersist]
    public function onPrePersist(): void { $this->creeLe = new \DateTimeImmutable(); }

    public function getId(): ?int { return $this->id; }
    public function getNote(): int { return $this->note; }
    public function setNote(int $note): static { $this->note = max(1, min(5, $note)); return $this; }
    public function getCommentaire(): ?string { return $this->commentaire; }
    public function setCommentaire(?string $c): static { $this->commentaire = $c ? strip_tags($c) : null; return $this; }
    public function getCreeLe(): ?\DateTimeImmutable { return $this->creeLe; }
    public function getAnnonce(): ?Annonce { return $this->annonce; }
    public function setAnnonce(?Annonce $annonce): static { $this->annonce = $annonce; return $this; }
    public function getAuteur(): ?User { return $this->auteur; }
    public function setAuteur(?User $auteur): static { $this->auteur = $auteur; return $this; }
}
