<?php

namespace App\Repository;

use App\Dto\Entity\Person\PersonQueryDto;
use App\Entity\Person;
use App\Repository\Traits\ActionTrait;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Person>
 */
class PersonRepository extends ServiceEntityRepository
{
    use ActionTrait;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Person::class);
    }

    public function filterByQueryParams(PersonQueryDto $personQueryDto): array
    {
        $search = $personQueryDto->search;
        $offset = $personQueryDto->offset ?? 0;
        $limit = $personQueryDto->limit;
        $sortBy = $personQueryDto->sortBy;
        $order = $personQueryDto->order;
        $gender = $personQueryDto->gender ?? null;
        $specialties = $personQueryDto->specialties ?: null;
        $specialtiesArr = array_map('intval', explode(',', $specialties));
        $queryBuilder = $this->createQueryBuilder('p')->where('1 = 1');

        if (!empty($search)) {
            $search = trim(strtolower($search));
            $queryBuilder
                ->where($queryBuilder->expr()->like('LOWER(p.firstname)', ':search'))
                ->orWhere($queryBuilder->expr()->like('LOWER(p.lastname)', ':search'))
                ->orWhere($queryBuilder->expr()->like('LOWER(p.internationalName)', ':search'))
                ->setParameter('search', "%{$search}%");
        }
        if ($sortBy === 'age') {
            $queryBuilder
                ->andWhere('p.birthday IS NOT NULL');
        }
        if ($gender !== null) {
            $queryBuilder
                ->andWhere('p.gender = :gender')
                ->setParameter('gender', $gender);
        }
        if ($specialties !== null) {
            $specialtiesFiltered = $this->filterBySpecialtyIds($specialtiesArr);

            $specilatyIds = array_column($specialtiesFiltered, 'id');
            if (!empty($specilatyIds)) {
                $queryBuilder->andWhere('p.id IN (:ids)')
                    ->setParameter('ids', $specilatyIds);
            } else {
                return [];
            }
        }
        $queryBuilder
            ->orderBy("p.{$sortBy}", $order);
        if ($limit !== 0) {
            $queryBuilder
                ->setMaxResults($limit)
                ->setFirstResult($offset);
        }

        return $queryBuilder->getQuery()->getResult();
    }

    public function countByQueryParams(PersonQueryDto $personQueryDto): int
    {
        $search = $personQueryDto->search;
        $gender = $personQueryDto->gender ?? null;
        $specialties = $personQueryDto->specialties ?: null;
        $specialtiesArr = array_map('intval', explode(',', $specialties));

        $qb = $this->createQueryBuilder('p')
            ->select('COUNT(p.id)')
            ->where('1 = 1');

        if (!empty($search)) {
            $search = trim(strtolower($search));
            $qb
                ->andWhere(
                    $qb->expr()->orX(
                        $qb->expr()->like('LOWER(p.firstname)', ':search'),
                        $qb->expr()->like('LOWER(p.lastname)', ':search'),
                        $qb->expr()->like('LOWER(p.internationalName)', ':search')
                    )
                )
                ->setParameter('search', "%{$search}%");
        }

        if ($personQueryDto->sortBy === 'age') {
            $qb->andWhere('p.birthday IS NOT NULL');
        }

        if ($gender !== null) {
            $qb
                ->andWhere('p.gender = :gender')
                ->setParameter('gender', $gender);
        }

        if ($specialties !== null) {
            $specialtiesFiltered = $this->filterBySpecialtyIds($specialtiesArr);
            $specilatyIds = array_column($specialtiesFiltered, 'id');

            if (!empty($specilatyIds)) {
                $qb->andWhere('p.id IN (:ids)')
                    ->setParameter('ids', $specilatyIds);
            } else {
                return 0;
            }
        }

        return (int) $qb->getQuery()->getSingleScalarResult();
    }


    public function total(): int
    {
        return $this
            ->createQueryBuilder('p')
            ->select('COUNT(p.id)')
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function findBySlug(string $slug): ?Person
    {
        $person = $this->findOneBy(['slug' => $slug]);

        return $person;
    }

    public function findWithSimilarSpecialties(int $personId, int $count): array
    {
        $connection = $this->getEntityManager()->getConnection();
        $query = 'SELECT p.*, (
            SELECT COUNT(*)
            FROM jsonb_array_elements_text(p.specialties::jsonb) AS ps
            WHERE tp::int IN (
                SELECT (jsonb_array_elements_text(tp.specialties::jsonb))::int
                )
            ) AS common_specialties_count
                FROM person p
                CROSS JOIN (
                    SELECT specialties
                    FROM person
                    WHERE id = :personId
                ) tp
            WHERE p.id != :personId
            AND EXISTS (
                SELECT 1
                FROM jsonb_array_elements_text(p.specialties::jsonb) ps
                WHERE ps::int IN (
                    SELECT (jsonb_array_elements_text(tp.specialties::jsonb))::int
                )
            )
            ORDER BY common_specialties_count DESC
            LIMIT :count;
        ';

        $stmt = $connection->prepare($query);
        $stmt->bindValue('personId', $personId, \PDO::PARAM_INT);
        $stmt->bindValue('count', $count, \PDO::PARAM_INT);
        $result = $stmt->executeQuery();

        return $result->fetchAllAssociative();
    }

    private function filterBySpecialtyIds(array $specialties): array
    {
        if (empty($specialties)) {
            return [];
        }

        $conditions = [];
        $params = [];

        foreach ($specialties as $index => $specialtyId) {
            $paramName = "specialty{$index}";
            $conditions[] = "specialties::jsonb @> :$paramName::jsonb";
            $params[$paramName] = json_encode([$specialtyId]);
        }

        $whereClause = implode(' OR ', $conditions);
        $sql = <<<SQL
        SELECT *
        FROM person
        WHERE $whereClause
        SQL;

        $connection = $this->getEntityManager()->getConnection();
        $stmt = $connection->prepare($sql);
        $result = $stmt->executeQuery($params);

        return $result->fetchAllAssociative();
    }

}
