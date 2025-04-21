<?php
declare(strict_types=1);

namespace App\Serializer;

class CircularReferenceHandler{
  public function __invoke($object): mixed
  {
    return $object->getId();
  }
}