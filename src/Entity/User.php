<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: '`user`')]
#[ORM\HasLifecycleCallbacks]
#[UniqueEntity(fields: ['email'], message: 'Cet email est déjà utilisé.')]
#[ApiResource(
    operations: [new Get(), new GetCollection()],
    normalizationContext: ['groups' => ['user:read']],
    security: "is_granted('ROLE_USER')"
)]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    #[Groups(['user:read'])]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 180, unique: true)]
    #[Assert\NotBlank(message: "L'email est obligatoire.")]
    #[Assert\Email(message: "L'email {{ value }} n'est pas valide.")]
    #[Groups(['user:read'])]
    private ?string $email = null;

    #[ORM\Column(type: 'json')]
    #[Groups(['user:read'])]
    private array $roles = [];

    #[ORM\Column(type: 'string')]
    private ?string $password = null;

    #[ORM\Column(type: 'string', length: 100)]
    #[Assert\NotBlank(message: 'Le prénom est obligatoire.')]
    #[Assert\Length(max: 100)]
    #[Groups(['user:read'])]
    private ?string $prenom = null;

    #[ORM\Column(type: 'string', length: 100)]
    #[Assert\NotBlank(message: 'Le nom est obligatoire.')]
    #[Assert\Length(max: 100)]
    #[Groups(['user:read'])]
    private ?string $nom = null;

    #[ORM\Column(type: 'string', length: 20, nullable: true)]
    #[Assert\Regex(pattern: '/^[0-9\+\-\s]{7,20}$/', message: "Numéro de téléphone invalide.")]
    #[Groups(['user:read'])]
    private ?string $telephone = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    #[Groups(['user:read'])]
    private ?string $photoProfil = null;

    #[ORM\Column(type: 'datetime_immutable')]
    #[Groups(['user:read'])]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(type: 'string', length: 100, nullable: true)]
    private ?string $resetToken = null;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private ?\DateTimeImmutable $resetTokenExpiresAt = null;

    #[ORM\OneToMany(mappedBy: 'proprietaire', targetEntity: Colocation::class)]
    private Collection $colocations;

    #[ORM\OneToMany(mappedBy: 'locataire', targetEntity: Chambre::class)]
    private Collection $chambres;

    #[ORM\OneToMany(mappedBy: 'expediteur', targetEntity: Message::class)]
    private Collection $messagesEnvoyes;

    #[ORM\OneToMany(mappedBy: 'destinataire', targetEntity: Message::class)]
    private Collection $messagesRecus;

    #[ORM\OneToMany(mappedBy: 'assigne', targetEntity: Tache::class)]
    private Collection $taches;

    #[ORM\OneToMany(mappedBy: 'user', targetEntity: Notification::class)]
    private Collection $notifications;

    #[ORM\OneToMany(mappedBy: 'locataire', targetEntity: EvaluationLocataire::class)]
    private Collection $evaluations;

    public function __construct()
    {
        $this->colocations = new ArrayCollection();
        $this->chambres = new ArrayCollection();
        $this->messagesEnvoyes = new ArrayCollection();
        $this->messagesRecus = new ArrayCollection();
        $this->taches = new ArrayCollection();
        $this->notifications = new ArrayCollection();
        $this->evaluations = new ArrayCollection();
    }

    #[ORM\PrePersist]
    public function onPrePersist(): void
    {
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): ?int { return $this->id; }

    public function getEmail(): ?string { return $this->email; }
    public function setEmail(string $email): static
    {
        $this->email = strtolower(trim($email));
        return $this;
    }

    public function getUserIdentifier(): string { return (string) $this->email; }

    public function getRoles(): array
    {
        $roles = $this->roles;
        $roles[] = 'ROLE_USER';
        return array_unique($roles);
    }
    public function setRoles(array $roles): static { $this->roles = $roles; return $this; }

    public function getPassword(): ?string { return $this->password; }
    public function setPassword(string $password): static { $this->password = $password; return $this; }

    public function eraseCredentials(): void {}

    public function getPrenom(): ?string { return $this->prenom; }
    public function setPrenom(string $prenom): static
    {
        $this->prenom = htmlspecialchars(strip_tags($prenom), ENT_QUOTES, 'UTF-8');
        return $this;
    }

    public function getNom(): ?string { return $this->nom; }
    public function setNom(string $nom): static
    {
        $this->nom = htmlspecialchars(strip_tags($nom), ENT_QUOTES, 'UTF-8');
        return $this;
    }

    public function getNomComplet(): string { return $this->prenom . ' ' . $this->nom; }

    public function getTelephone(): ?string { return $this->telephone; }
    public function setTelephone(?string $telephone): static
    {
        $this->telephone = $telephone ? preg_replace('/[^0-9\+\-\s]/', '', $telephone) : null;
        return $this;
    }

    public function getPhotoProfil(): ?string { return $this->photoProfil; }
    public function setPhotoProfil(?string $photoProfil): static { $this->photoProfil = $photoProfil; return $this; }

    public function getCreatedAt(): ?\DateTimeImmutable { return $this->createdAt; }

    public function getResetToken(): ?string { return $this->resetToken; }
    public function setResetToken(?string $resetToken): static { $this->resetToken = $resetToken; return $this; }

    public function getResetTokenExpiresAt(): ?\DateTimeImmutable { return $this->resetTokenExpiresAt; }
    public function setResetTokenExpiresAt(?\DateTimeImmutable $resetTokenExpiresAt): static { $this->resetTokenExpiresAt = $resetTokenExpiresAt; return $this; }

    public function isProprietaire(): bool { return in_array('ROLE_PROPRIETAIRE', $this->getRoles(), true); }

    public function getColocations(): Collection { return $this->colocations; }
    public function getChambres(): Collection { return $this->chambres; }
    public function getMessagesEnvoyes(): Collection { return $this->messagesEnvoyes; }
    public function getMessagesRecus(): Collection { return $this->messagesRecus; }
    public function getTaches(): Collection { return $this->taches; }
    public function getNotifications(): Collection { return $this->notifications; }
    public function getEvaluations(): Collection { return $this->evaluations; }
}
