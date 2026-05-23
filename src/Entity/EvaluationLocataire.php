<?php

namespace App\Entity;

use App\Repository\EvaluationLocataireRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: EvaluationLocataireRepository::class)]
#[ORM\HasLifecycleCallbacks]
class EvaluationLocataire
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

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'evaluations')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $locataire = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $proprietaire = null;

    #[ORM\ManyToOne(targetEntity: Colocation::class)]
    #[ORM\JoinColumn(nullable: false)]
    private ?Colocation $colocation = null;

    #[ORM\PrePersist]
    public function onPrePersist(): void { $this->creeLe = new \DateTimeImmutable(); }

    public function getId(): ?int { return $this->id; }
    public function getNote(): int { return $this->note; }
    public function setNote(int $note): static { $this->note = max(1, min(5, $note)); return $this; }
    public function getCommentaire(): ?string { return $this->commentaire; }
    public function setCommentaire(?string $commentaire): static { $this->commentaire = $commentaire ? strip_tags($commentaire) : null; return $this; }
    public function getCreeLe(): ?\DateTimeImmutable { return $this->creeLe; }
    public function getLocataire(): ?User { return $this->locataire; }
    public function setLocataire(?User $locataire): static { $this->locataire = $locataire; return $this; }
    public function getProprietaire(): ?User { return $this->proprietaire; }
    public function setProprietaire(?User $proprietaire): static { $this->proprietaire = $proprietaire; return $this; }
    public function getColocation(): ?Colocation { return $this->colocation; }
    public function setColocation(?Colocation $colocation): static { $this->colocation = $colocation; return $this; }
}
