<?php

namespace App\Repository;

use App\Dto\Entity\Film\FilmQueryDto;
use App\Entity\Film;
use App\Repository\Traits\ActionTrait;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Film>
 */
class FilmRepository extends ServiceEntityRepository
{
    use ActionTrait;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Film::class);
    }

    public function filterByQueryParams(FilmQueryDto $filmQueryDto): array
    {
        $search = $filmQueryDto->search;
        $offset = $filmQueryDto->offset;
        $limit = $filmQueryDto->limit;
        $sortBy = $filmQueryDto->sortBy;
        $order = $filmQueryDto->order;

        $queryBuilder = $this->createQueryBuilder('f')->where('1 = 1');

        if (!empty($search)) {
            $search = trim(strtolower($search));
            $queryBuilder
                ->where($queryBuilder->expr()->like('LOWER(f.name)', ':search'))
                ->orWhere($queryBuilder->expr()->like('LOWER(f.internationalName)', ':search'))
                ->setParameter('search', "%{$search}%");
        }
        $queryBuilder
            ->orderBy("f.{$sortBy}", $order);
        if ($limit !== 0) {
            $queryBuilder
                ->setMaxResults($limit)
                ->setFirstResult($offset);
        }

        return $queryBuilder->getQuery()->getResult();
    }

    public function total(): int
    {
        return $this
            ->createQueryBuilder('f')
            ->select('COUNT(f.id)')
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function findLatest(int $count): array
    {
        return $this
            ->createQueryBuilder('f')
            ->orderBy('f.releaseYear', 'DESC')
            ->setMaxResults($count)
            ->getQuery()
            ->getResult();
    }

    public function findTop(int $count): array
    {
        return $this
            ->createQueryBuilder('f')
            ->orderBy('f.rating', 'DESC')
            ->where('f.rating IS NOT NULL AND f.rating >= 4')
            ->setMaxResults($count)
            ->getQuery()
            ->getResult();
    }

    public function findBySlug(string $slug): ?Film
    {
        return $this->findOneBy(['slug' => $slug]);
    }

    public function findWithSimilarGenres(int $filmId, int $count): array
    {
        $connection = $this->getEntityManager()->getConnection();
        $query = 'SELECT
        f.id,
        f.genres,
        f.directed_by_id,
        f.producer_id,
        f.writer_id,
        f.composer_id,
        f.publisher_id,
        f.name,
        f.international_name,
        f.release_year,
        f.rating,
        f.duration,
        f.description,
        f.poster,
        f.created_at,
        f.updated_at,
        f.age,
        f.slug,
        f.country,
        f.budget,
        f.fees,
        (
            SELECT COUNT(*)
            FROM jsonb_array_elements_text(f.genres::jsonb) fg
            WHERE fg IN (
                SELECT jsonb_array_elements_text(tf.genres::jsonb)
            )
        ) AS common_genres_count
        FROM film f
        CROSS JOIN (
            SELECT id, name, genres
            FROM film
            WHERE id = :filmId
        ) tf
        WHERE f.id != tf.id
        AND f.genres::jsonb ?| array(
            SELECT jsonb_array_elements_text(tf.genres::jsonb)
        )
        ORDER BY common_genres_count DESC
        LIMIT :count';

        $stmt = $connection->prepare($query);
        $stmt->bindValue('filmId', $filmId, \PDO::PARAM_INT);
        $stmt->bindValue('count', $count, \PDO::PARAM_INT);
        $result = $stmt->executeQuery();
        return $result->fetchAllAssociative();
    }
}
