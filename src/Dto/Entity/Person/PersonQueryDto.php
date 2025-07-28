<?php

namespace App\Dto\Entity\Person;

use OpenApi\Attributes as OA;

class PersonQueryDto
{
    public function __construct(
        #[OA\Property(example: 5)]
        public readonly ?int $limit = 5,
        #[OA\Property(example: 0)]
        public readonly ?int $offset = 0,
        #[OA\Property(example: 'John')]
        public ?string $search = null,
        #[OA\Property(example: 'firstname')]
        public ?string $sortBy = null,
        #[OA\Property(example: 'asc')]
        public ?string $order = 'ASC',
        #[OA\Property(example: 'ru')]
        public ?string $locale = null,
        #[OA\Property(example: 'gender')]
        public ?string $gender = null,
        #[OA\Property(example: '1,2')]
        public ?string $specialties = null,
    ) {
    }
}
