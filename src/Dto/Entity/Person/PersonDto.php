<?php

namespace App\Dto\Entity\Person;

use OpenApi\Attributes as OA;

class PersonDto
{
    public function __construct(
        #[OA\Property(example: 'Peter')]
        public readonly ?string $firstname = '',
        #[OA\Property(example: 'Jackson')]
        public readonly ?string $lastname = '',
        #[OA\Property(example: 'Peter Jackson')]
        public readonly ?string $internationalName = '',
        #[OA\Property(example: 1)]
        public readonly ?int $genderId = 1,
        #[OA\Property(example: '1986-06-05')]
        public readonly ?\DateTimeImmutable $birthday = null,
        #[OA\Property(example: [1, 2])]
        public readonly ?array $specialtyIds = [],
        #[OA\Property(example: 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.')]
        public readonly ?string $bio = '',
        #[OA\Property(example: 'avatar.jpg')]
        public readonly ?string $avatar = '',
        #[OA\Property(example: 'cover.jpg')]
        public readonly ?string $cover = '',
    ) {
    }
}
