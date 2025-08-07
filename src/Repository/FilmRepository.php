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
        $search = $filmQueryDto->search ?: null;
        $offset = $filmQueryDto->offset ?: null;
        $limit = $filmQueryDto->limit ?: null;
        $sortBy = $filmQueryDto->sortBy ?: null;
        $order = $filmQueryDto->order ?: null;
        $genres = $filmQueryDto->genres ?: null;
        $countries = $filmQueryDto->countries ?: null;
        $genresArr = array_map('intval', explode(',', $genres));

        $countryCodes = explode(',', $countries);
        $qb = $this->createQueryBuilder('f')->where('1 = 1');

        if ($search !== null) {
            $search = trim(strtolower($search));
            $qb->andWhere($qb->expr()->like('LOWER(f.name)', ':search'))
                ->orWhere($qb->expr()->like('LOWER(f.internationalName)', ':search'))
                ->setParameter('search', "%{$search}%");
        }
        $qb->orderBy("f.{$sortBy}", $order);
        if ($limit !== null) {
            $qb
                ->setMaxResults($limit)
                ->setFirstResult($offset);
        }
        if ($genres !== null) {
            $genreFiltered = $this->filterByGenreIds($genresArr);

            $genreIds = array_column($genreFiltered, 'id');
            if (!empty($genreIds)) {
                $qb->andWhere('f.id IN (:ids)')
                    ->setParameter('ids', $genreIds);
            } else {
                return [];
            }
        }
        if ($countries !== null) {
            $qb
                ->andWhere($qb->expr()->in('f.country', ':country'))
                ->setParameter('country', $countryCodes);
        }

        return $qb->getQuery()->getResult();
    }

    public function countByQueryParams(FilmQueryDto $filmQueryDto): int
    {
        $search = $filmQueryDto->search ?: null;
        $genres = $filmQueryDto->genres ?: null;
        $countries = $filmQueryDto->countries ?: null;
        $genresArr = array_map('intval', explode(',', $genres));
        $countryCodes = explode(',', $countries);

        $qb = $this->createQueryBuilder('f')
            ->select('COUNT(f.id)')
            ->where('1 = 1');

        if ($search !== null) {
            $search = trim(strtolower($search));
            $qb->andWhere(
                $qb->expr()->orX(
                    $qb->expr()->like('LOWER(f.name)', ':search'),
                    $qb->expr()->like('LOWER(f.internationalName)', ':search')
                )
            )
                ->setParameter('search', "%{$search}%");
        }

        if ($genres !== null) {
            $genreFiltered = $this->filterByGenreIds($genresArr);
            $genreIds = array_column($genreFiltered, 'id');
            if (!empty($genreIds)) {
                $qb->andWhere('f.id IN (:ids)')
                    ->setParameter('ids', $genreIds);
            } else {
                return 0;
            }
        }

        if ($countries !== null) {
            $qb->andWhere($qb->expr()->in('f.country', ':country'))
                ->setParameter('country', $countryCodes);
        }

        return (int) $qb->getQuery()->getSingleScalarResult();
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
        $film = $this->findOneBy(['slug' => $slug]);

        return $film;
    }

    public function findWithSimilarGenres(int $filmId, int $count): array
    {
        $connection = $this->getEntityManager()->getConnection();
        $query = 'SELECT f.*, (
            SELECT COUNT(*)
            FROM jsonb_array_elements_text(f.genres::jsonb) AS fg
            WHERE fg::int IN (
                SELECT (jsonb_array_elements_text(tf.genres::jsonb))::int
                )
            ) AS common_genres_count
                FROM film f
                CROSS JOIN (
                    SELECT genres
                    FROM film
                    WHERE id = :filmId
                ) tf
            WHERE f.id != :filmId
            AND EXISTS (
                SELECT 1
                FROM jsonb_array_elements_text(f.genres::jsonb) fg
                WHERE fg::int IN (
                    SELECT (jsonb_array_elements_text(tf.genres::jsonb))::int
                )
            )
            ORDER BY common_genres_count DESC
            LIMIT :count;
        ';

        $stmt = $connection->prepare($query);
        $stmt->bindValue('filmId', $filmId, \PDO::PARAM_INT);
        $stmt->bindValue('count', $count, \PDO::PARAM_INT);
        $result = $stmt->executeQuery();

        return $result->fetchAllAssociative();
    }

    private function filterByGenreIds(array $genres): array
    {
        if (empty($genres)) {
            return [];
        }

        $conditions = [];
        $params = [];

        foreach ($genres as $index => $genreId) {
            $paramName = "genre{$index}";
            $conditions[] = "genres::jsonb @> :$paramName::jsonb";
            $params[$paramName] = json_encode([(int) $genreId]);
        }

        $whereClause = implode(' OR ', $conditions);
        $sql = <<<SQL
        SELECT *
        FROM film
        WHERE $whereClause
        SQL;

        $connection = $this->getEntityManager()->getConnection();
        $stmt = $connection->prepare($sql);
        $result = $stmt->executeQuery($params);

        return $result->fetchAllAssociative();
    }
}
