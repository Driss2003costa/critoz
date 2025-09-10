<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use phpDocumentor\Reflection\Types\Boolean;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;

#[ORM\Entity(repositoryClass: UserRepository::class)]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;
    #[ORM\Column(type: 'integer')]
    private int $verified = 0; // 0 = non vérifié, 1 = vérifié
    #[ORM\Column(type: 'boolean')]
    private $is_banned = false; // 0 = non vérifié, 1 = vérifié

    #[ORM\Column(type: "string", length: 255, nullable: true)]
    private ?string $banReason = null;

    #[ORM\Column(type: "integer")]
    private ?string $warn = null;

    #[ORM\Column(type: "string", length: 255, nullable: true)]
    private ?string $warn_reason = null;


    public function getWarn(): ?string { return $this->warn; }
    public function setWarn(?int $warn): self {
        $this->warn = $warn;
        return $this; }

    public function getWarnReason(): ?string { return $this->warn; }
    public function setWarnReason(?string $warn_reason): self {
        $this->warn = $warn_reason;
        return $this; }


    public function getBanReason(): ?string { return $this->banReason; }
    public function setBanReason(?string $reason): self { $this->banReason = $reason; return $this; }

    public function getBanned(): bool
    {
        return $this->is_banned;
    }

    public function setBanned(bool $is_banned): self
    {
        $this->is_banned = $is_banned;
        return $this;
    }

    public function getVerified(): int
    {
        return $this->verified;
    }

    public function setVerified(int $verified): self
    {
        $this->verified = $verified;
        return $this;
    }
    #[ORM\Column(length: 180, unique: true)]
    private string $email;

    #[ORM\Column(length: 255)]
    private string $username;

    #[ORM\Column(length: 255)]
    private string $password;

    #[ORM\Column(length: 7, nullable: true)]
    private ?string $bannerColor = null;


    #[ORM\Column(length: 255, nullable: true)]
    private ?string $profilePicture = null;

    #[ORM\OneToMany(mappedBy: 'user', targetEntity: Movie::class)]
    private Collection $movies;

    public function __construct()
    {
        $this->movies = new ArrayCollection();
    }

    public function getBannerColor(): ?string
    {
        return $this->bannerColor;
    }

    public function setBannerColor(?string $bannerColor): self
    {
        $this->bannerColor = $bannerColor;
        return $this;
    }

    // --- Getters & Setters ---
    public function getId(): ?int { return $this->id; }

    public function getEmail(): string { return $this->email; }
    public function setEmail(string $email): self { $this->email = $email; return $this; }

    public function getUsername(): string { return $this->username; }
    public function setUsername(string $username): self { $this->username = $username; return $this; }

    public function getPassword(): string { return $this->password; }
    public function setPassword(string $password): self { $this->password = $password; return $this; }

    public function getCountry(): ?string { return $this->country; }
    public function setCountry(?string $country): self { $this->country = $country; return $this; }

    public function getProfilePicture(): ?string { return $this->profilePicture; }
    public function setProfilePicture(?string $profilePicture): self { $this->profilePicture = $profilePicture; return $this; }

    /** @return Collection<int, Movie> */
    public function getMovies(): Collection { return $this->movies; }

    // --- Security Methods ---
    public function getUserIdentifier(): string
    {
        return $this->email;
    }

    public function eraseCredentials(): void
    {
        // Si tu stockes des infos sensibles temporairement
    }

    #[ORM\Column(type: 'json')]
    private array $roles = [];

    public function getRoles(): array
    {
        // Garantit que ROLE_USER est toujours présent
        $roles = $this->roles;
        $roles[] = 'ROLE_USER';
        return array_unique($roles);
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;
        return $this;
    }

}
