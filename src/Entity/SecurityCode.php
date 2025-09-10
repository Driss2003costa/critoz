<?php
namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Repository\SecurityCodeRepository;

#[ORM\Entity(repositoryClass: SecurityCodeRepository::class)]
class SecurityCode
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type:"integer")]
    private ?int $id = null;

    #[ORM\Column(length:6)]
    private string $code;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable:false, onDelete:"CASCADE")]
    private User $user;

    #[ORM\Column(type:"datetime")]
    private \DateTimeInterface $expiresAt;

    #[ORM\Column(type:"string", length:20)]
    private string $type = 'email_verification'; // facultatif, pour gérer différents usages

    public function getId(): ?int { return $this->id; }
    public function getCode(): string { return $this->code; }
    public function setCode(string $code): self { $this->code = $code; return $this; }
    public function getUser(): User { return $this->user; }
    public function setUser(User $user): self { $this->user = $user; return $this; }
    public function getExpiresAt(): \DateTimeInterface { return $this->expiresAt; }
    public function setExpiresAt(\DateTimeInterface $expiresAt): self { $this->expiresAt = $expiresAt; return $this; }
    public function getType(): string { return $this->type; }
    public function setType(string $type): self { $this->type = $type; return $this; }
}
