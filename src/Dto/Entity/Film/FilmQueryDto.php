<?php
namespace App\Dto\Entity\Film;
use OpenApi\Attributes as OA;

class FilmQueryDto
{
  public function __construct(
    #[OA\Property(example: 5)]
    public readonly ?int $limit = 5,
    #[OA\Property(example: 0)]
    public readonly ?int $offset = 0,
    #[OA\Property(example: 'star')]
    public ?string $search = null,
    #[OA\Property(example: 'name')]
    public ?string $sortBy = 'name',
    #[OA\Property(example: 'asc')]
    public ?string $order = 'ASC',
    #[OA\Property(example: 'ru')]
    public ?string $locale = null,
  ) {
  }

}