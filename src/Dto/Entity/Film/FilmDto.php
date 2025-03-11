<?php
namespace App\Dto\Entity\Film;
use OpenApi\Attributes as OA;
class FilmDto
{
  public function __construct(
    #[OA\Property(example: 'Star Wars')]
    public readonly ?string $name = null,
    #[OA\Property(example: 'The Empire Strikes Back')]
    public readonly ?string $slogan = null,
    #[OA\Property(example: [1, 2, 3])]
    public readonly ?array $genreIds = [],
    #[OA\Property(example: 1987)]
    public readonly ?int $releaseYear = null,
    #[OA\Property(example: [1, 2, 3])]
    public readonly ?array $actorIds = null,
    #[OA\Property(example: 4)]
    public readonly ?int $directorId = null, 
    #[OA\Property(example: 3)]
    public readonly ?int $producerId = null,
    #[OA\Property(example: 4)]
    public readonly ?int $writerId = null,
    #[OA\Property(example: 5)]
    public readonly ?int $composerId = null,
    #[OA\Property(example: 18)]
    public readonly ?int $age = null,
    #[OA\Property(example: 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.')]
    public readonly ?string $description = null,
    #[OA\Property(example: '2:30:00')]
    public readonly ?\DateTimeImmutable $duration = null,
    #[OA\Property(example: 'https://example.com/cover.jpg')]
    public readonly ?string $cover = null,
    #[OA\Property(example: 'https://example.com/poster.jpg')]
    public readonly ?string $poster = null,
    #[OA\Property(example: 'https://youtube.com/watch?v=dQw4w9WgXcQ')]
    public readonly ?string $trailer = null,
    #[OA\Property(example: ['John Doe', 'Jane Smith'])]
    public readonly ?array $roleNames = [],
    
  ) {
  }
}