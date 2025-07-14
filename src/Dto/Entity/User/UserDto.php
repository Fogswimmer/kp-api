<?php

namespace App\Dto\Entity\User;

use OpenApi\Attributes as OA;

class UserDto
{
    public function __construct(
        #[OA\Property(example: 'peter')]
        public readonly ?string $username = '',
        #[OA\Property(example: 'Peter Jackson')]
        public readonly ?string $displayName = '',
        #[OA\Property(example: 'peter@gmail.com')]
        public readonly ?string $email = '',
        #[OA\Property(example: 'password')]
        public readonly ?string $password = '',
        #[OA\Property(example: 25)]
        public readonly ?int $age = null,
        #[OA\Property(example: 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.')]
        public readonly ?string $about = null,
    ) {
    }
}
