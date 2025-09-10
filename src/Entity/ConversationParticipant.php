<?php


namespace App\Entity;

use App\Repository\ConversationParticipantRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ConversationParticipantRepository::class)]
#[ORM\Table(name: "conversation_participants")]
class ConversationParticipant
{
    #[ORM\Id, ORM\GeneratedValue, ORM\Column(type: 'integer')]
    private int $id;

    #[ORM\ManyToOne(targetEntity: Conversation::class, inversedBy: 'participants')]
    private Conversation $conversation;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false)]
    private User $participant; // <-- correspond à user_id dans la table

    #[ORM\Column(type: 'datetime')]
    private \DateTimeInterface $joinedAt;

    public function __construct()
    {
        $this->joinedAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getConversation(): ?Conversation
    {
        return $this->conversation;
    }

    public function setConversation(?Conversation $conversation): self
    {
        $this->conversation = $conversation;
        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;
        return $this;
    }

    public function getJoinedAt(): \DateTimeInterface
    {
        return $this->joinedAt;
    }

    public function setJoinedAt(\DateTimeInterface $joinedAt): self
    {
        $this->joinedAt = $joinedAt;
        return $this;
    }
}
