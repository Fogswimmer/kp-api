<?php

namespace App\Repository\Traits;

trait ActionTrait
{

  public function store($object): void
  {
    $this->getEntityManager()->persist($object);
    $this->getEntityManager()->flush();
  }

  public function remove($object): void
  {
    $this->getEntityManager()->remove($object);
    $this->getEntityManager()->flush();
  }
}