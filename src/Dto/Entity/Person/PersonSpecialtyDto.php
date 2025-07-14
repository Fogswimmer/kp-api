<?php

namespace App\Dto\Entity\Person;

use OpenApi\Attributes as OA;

class PersonSpecialtyDto
{
    public function __construct(
        #[OA\Property(example: 1)]
        public readonly ?int $specialtyId = null,
    ) {
    }
}
