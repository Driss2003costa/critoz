<?php

// src/Repository/ReviewRepository.php

namespace App\Repository;

use App\Entity\Review;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Review>
 *
 * @method Review|null find($id, $lockMode = null, $lockVersion = null)
 * @method Review|null findOneBy(array $criteria, array $orderBy = null)
 * @method Review[]    findAll()
 * @method Review[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ReviewRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Review::class);
    }

    // Exemple : récupérer toutes les reviews d’un film
    public function findByMovieId(int $movieId): array
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.movieId = :movieId')
            ->setParameter('movieId', $movieId)
            ->orderBy('r.id', 'DESC')
            ->getQuery()
            ->getResult();
    }
}
