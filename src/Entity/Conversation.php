<?php


namespace App\Entity;

use App\Repository\ConversationRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ConversationRepository::class)]
#[ORM\Table(name: 'conversations')]
class Conversation
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\ManyToMany(targetEntity: User::class)]
    #[ORM\JoinTable(name: 'conversation_participants')]
    private Collection $participants;

    #[ORM\OneToMany(mappedBy: 'conversation', targetEntity: Message::class, cascade: ['remove'])]
    private Collection $messages;

    #[ORM\Column(type: 'datetime')]
    private \DateTimeInterface $createdAt;

    public function __construct()
    {
        $this->participants = new ArrayCollection();
        $this->messages = new ArrayCollection();
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    /** @return Collection<int, User> */
    public function getParticipants(): Collection
    {
        return $this->participants;
    }

    public function addParticipant(User $user): self
    {
        if (!$this->participants->contains($user)) {
            $this->participants->add($user);
        }
        return $this;
    }

    public function removeParticipant(User $user): self
    {
        $this->participants->removeElement($user);
        return $this;
    }

    /** @return Collection<int, Message> */
    public function getMessages(): Collection
    {
        return $this->messages;
    }

    public function getCreatedAt(): \DateTimeInterface
    {
        return $this->createdAt;
    }
}
