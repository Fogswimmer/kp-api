<?php

namespace App\Repository;

use App\Entity\ActorRole;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use App\Repository\Traits\ActionTrait;
/**
 * @extends ServiceEntityRepository<ActorRole>
 */
class ActorRoleRepository extends ServiceEntityRepository
{
    use ActionTrait;
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ActorRole::class);
    }

//    /**
//     * @return ActorRole[] Returns an array of ActorRole objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('a')
//            ->andWhere('a.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('a.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?ActorRole
//    {
//        return $this->createQueryBuilder('a')
//            ->andWhere('a.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
