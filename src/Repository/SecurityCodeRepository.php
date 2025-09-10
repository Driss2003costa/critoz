<?php
namespace App\Repository;

use App\Entity\SecurityCode;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<SecurityCode>
 */
class SecurityCodeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SecurityCode::class);
    }

    /**
     * Trouve un code valide pour un utilisateur et un type donné
     */
    public function findValidCodeForUser(string $code, $userId, string $type = 'email_verification'): ?SecurityCode
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.code = :code')
            ->andWhere('s.user = :user')
            ->andWhere('s.type = :type')
            ->andWhere('s.expiresAt > :now')
            ->setParameters([
                'code' => $code,
                'user' => $userId,
                'type' => $type,
                'now' => new \DateTimeImmutable()
            ])
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * Supprime tous les codes expirés
     */
    public function removeExpired(): int
    {
        return $this->createQueryBuilder('s')
            ->delete()
            ->andWhere('s.expiresAt <= :now')
            ->setParameter('now', new \DateTimeImmutable())
            ->getQuery()
            ->execute();
    }
}
