<?php

namespace App\Repository;

use App\Entity\ConversationParticipant;
use App\Entity\User;
use App\Entity\Conversation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class ConversationParticipantRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ConversationParticipant::class);
    }

    // Vérifier si un utilisateur est bien participant
    public function isParticipant(User $user, Conversation $conversation): bool
    {
        return (bool) $this->createQueryBuilder('cp')
            ->where('cp.user = :user')
            ->andWhere('cp.conversation = :conversation')
            ->setParameter('user', $user)
            ->setParameter('conversation', $conversation)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
