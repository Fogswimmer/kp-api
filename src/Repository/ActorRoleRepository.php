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

}
