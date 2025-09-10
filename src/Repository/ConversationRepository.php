<?php

namespace App\Repository;

use App\Entity\Conversation;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class ConversationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Conversation::class);
    }

    /**
     * Récupérer toutes les conversations d’un utilisateur
     */
    public function findByUser(User $user): array
    {
        return $this->createQueryBuilder('c')
            ->where(':user MEMBER OF c.participants')
            ->setParameter('user', $user)
            ->getQuery()
            ->getResult();
    }

    /**
     * Vérifier si une conversation privée entre deux utilisateurs existe déjà
     */
    public function findPrivateConversation(User $user1, User $user2): ?Conversation
    {
        return $this->createQueryBuilder('c')
            ->where(':user1 MEMBER OF c.participants')
            ->andWhere(':user2 MEMBER OF c.participants')
            ->setParameter('user1', $user1)
            ->setParameter('user2', $user2)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
