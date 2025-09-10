<?php

namespace App\Entity;

use App\Repository\MessageRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: MessageRepository::class)]
#[ORM\Table(name: 'messages')]
class Message
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Conversation::class, inversedBy: 'messages')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?Conversation $conversation = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?User $sender = null;

    #[ORM\Column(type: 'text')]
    private string $body;

    #[ORM\Column(type: 'datetime')]
    private \DateTimeInterface $createdAt;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): ?int { return $this->id; }

    public function getConversation(): ?Conversation { return $this->conversation; }
    public function setConversation(?Conversation $conversation): self { $this->conversation = $conversation; return $this; }

    public function getSender(): ?User { return $this->sender; }
    public function setSender(?User $user): self { $this->sender = $user; return $this; }

    public function getBody(): string { return $this->body; }
    public function setBody(string $body): self { $this->body = $body; return $this; }

    public function getCreatedAt(): \DateTimeInterface { return $this->createdAt; }

    public function setCreatedAt(\DateTimeImmutable $param)
    {

    }
}
