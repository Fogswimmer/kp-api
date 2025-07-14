<?php

namespace App\Dto\Entity\Assessment;

use OpenApi\Attributes as OA;

class AssessmentDto
{
    public function __construct(
        #[OA\Property(example: 5)]
        public readonly ?int $rating = null,
        #[OA\Property(example: 'Good')]
        public readonly ?string $comment = '',
    ) {
    }
}
