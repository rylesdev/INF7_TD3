<?php

namespace App\Entity;

use App\Repository\PhotoAnnonceRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: PhotoAnnonceRepository::class)]
class PhotoAnnonce
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    #[Groups(['annonce:read'])]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 255)]
    #[Groups(['annonce:read'])]
    private ?string $filename = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    #[Groups(['annonce:read'])]
    private ?string $alt = null;

    #[ORM\Column(type: 'integer')]
    private int $position = 0;

    #[ORM\ManyToOne(targetEntity: Annonce::class, inversedBy: 'photos')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Annonce $annonce = null;

    public function getId(): ?int { return $this->id; }
    public function getFilename(): ?string { return $this->filename; }
    public function setFilename(string $filename): static { $this->filename = $filename; return $this; }
    public function getAlt(): ?string { return $this->alt; }
    public function setAlt(?string $alt): static { $this->alt = $alt; return $this; }
    public function getPosition(): int { return $this->position; }
    public function setPosition(int $position): static { $this->position = $position; return $this; }
    public function getAnnonce(): ?Annonce { return $this->annonce; }
    public function setAnnonce(?Annonce $annonce): static { $this->annonce = $annonce; return $this; }
}
