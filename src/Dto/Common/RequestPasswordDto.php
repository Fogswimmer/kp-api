<?php

namespace App\Dto\Common;

use OpenApi\Attributes as OA;

class RequestPasswordDto
{
    public function __construct(
        #[OA\Property(example: 'example@mail.com')]
        public readonly ?string $email = null,
        #[OA\Property(example: 'password')]
        public readonly ?string $password = null,
    ) {
    }
}
