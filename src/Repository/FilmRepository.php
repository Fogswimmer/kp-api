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

		$queryBuilder = $this->createQueryBuilder('f')->where('1 = 1');;

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

	public function findWithSimilarGenres(array $genreIds): array
	{
		$queryBuilder = $this->createQueryBuilder('f');

		$queryBuilder
			->where('f.genres @> :genreIds')
			->setParameter('genreIds', '{' . implode(',', $genreIds) . '}', \Doctrine\DBAL\Types\Types::STRING);

		return $queryBuilder->getQuery()->getResult();
	}
}
