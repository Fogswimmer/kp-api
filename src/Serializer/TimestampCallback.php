<?php

namespace App\Serializer;

use DateTimeInterface;

class TimestampCallback
{
  public function __invoke(null|string|DateTimeInterface $innerObject): DateTimeInterface|string|null
  {
    if ($innerObject === null) {
      return null;
    }

    if (!($innerObject instanceof DateTimeInterface)) {
      return $innerObject;
    }

    return $innerObject->format('Y-m-d');
  }
}