<?php


namespace App\Repository;

use App\Entity\Friendship;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Friendship>
 */
class FriendshipRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Friendship::class);
    }

    /**
     * Retourne les amis acceptés d’un utilisateur
     */
    public function findFriends(User $user): array
    {
        return $this->createQueryBuilder('f')
            ->where('f.requester = :user OR f.addressee = :user')
            ->andWhere('f.status = :status')
            ->setParameter('user', $user)
            ->setParameter('status', 'accepted')
            ->getQuery()
            ->getResult();
    }

    /**
     * Retourne les demandes en attente reçues par un utilisateur
     */
    public function findPendingRequests(User $user): array
    {
        return $this->createQueryBuilder('f')
            ->where('f.addressee = :user')
            ->andWhere('f.status = :status')
            ->setParameter('user', $user)
            ->setParameter('status', 'pending')
            ->getQuery()
            ->getResult();
    }

    /**
     * Vérifie si deux utilisateurs sont déjà amis
     */
    public function areFriends(User $user1, User $user2): bool
    {
        $result = $this->createQueryBuilder('f')
            ->where('(f.requester = :u1 AND f.addressee = :u2) OR (f.requester = :u2 AND f.addressee = :u1)')
            ->andWhere('f.status = :status')
            ->setParameter('u1', $user1)
            ->setParameter('u2', $user2)
            ->setParameter('status', 'accepted')
            ->getQuery()
            ->getOneOrNullResult();

        return $result !== null;
    }

    /**
     * Vérifie s’il existe une demande d’ami en cours
     */
    public function hasPendingRequest(User $sender, User $receiver): bool
    {
        $result = $this->createQueryBuilder('f')
            ->where('(f.requester = :sender AND f.addressee = :receiver)')
            ->andWhere('f.status = :status')
            ->setParameter('sender', $sender)
            ->setParameter('receiver', $receiver)
            ->setParameter('status', 'pending')
            ->getQuery()
            ->getOneOrNullResult();

        return $result !== null;
    }
}
