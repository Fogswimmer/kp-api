<?php

namespace App\Repository;

use App\Entity\Assessment;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use App\Repository\Traits\ActionTrait;
/**
 * @extends ServiceEntityRepository<Assessment>
 */
class AssessmentRepository extends ServiceEntityRepository
{
    use ActionTrait;
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Assessment::class);
    }
}
