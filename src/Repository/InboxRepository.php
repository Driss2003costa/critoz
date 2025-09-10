<?php


namespace App\Repository;


use App\Entity\Inbox;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\EntityManagerInterface;

class InboxRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry, EntityManagerInterface $em)
    {
        parent::__construct($registry, Inbox::class);
        $this->em = $em;
    }

    public function findInboxByUserId(int $userId): array
    {
        return $this->createQueryBuilder('m')
            ->leftJoin('m.sender', 's')
            ->addSelect('s')
            ->andWhere('m.receiver = :userId')
            ->setParameter('userId', $userId)
            ->orderBy('m.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function save(Inbox $message, bool $flush = true): void
    {
        $this->em->persist($message);
        if ($flush) {
            $this->em->flush();
        }
    }
}