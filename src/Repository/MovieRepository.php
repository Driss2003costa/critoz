<?php

namespace App\Repository;

use App\Entity\Movie;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Movie>
 *
 * @method Movie|null find($id, $lockMode = null, $lockVersion = null)
 * @method Movie|null findOneBy(array $criteria, array $orderBy = null)
 * @method Movie[]    findAll()
 * @method Movie[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MovieRepository extends ServiceEntityRepository
{


    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Movie::class);
    }

    // Exemple : récupérer les dernières critiques
    public function findLatestReviews(int $limit = 10): array
    {
        return $this->createQueryBuilder('m')
            ->andWhere('m.user IS NOT NULL')
            ->orderBy('m.id', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }
    // Exemple : récupérer toutes les critiques d'un utilisateur
    public function findByUserId(int $userId): array
    {
        return $this->createQueryBuilder('m')
            ->andWhere('m.user = :userId')
            ->setParameter('userId', $userId)
            ->orderBy('m.id', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findByMovieId(int $tmdbId): ?Movie
    {
        return $this->createQueryBuilder('m')
            ->andWhere('m.movieId = :tmdbId')
            ->setParameter('tmdbId', $tmdbId)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findByUserIdCustom(int $userId): array
    {
        $conn = $this->getEntityManager()->getConnection();

        $sql = '
        SELECT title_review, reviews, rating, movie_title, movie_id
        FROM movie
        WHERE user_id = :userId
        ORDER BY movie_id DESC
    ';

        $stmt = $conn->prepare($sql);
        $resultSet = $stmt->executeQuery(['userId' => $userId]);

        // Retourne un tableau associatif
        return $resultSet->fetchAllAssociative();
    }
}