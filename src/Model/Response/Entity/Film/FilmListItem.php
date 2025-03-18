<?php
namespace App\Model\Response\Entity\Film;

use OpenApi\Attributes as OA;

class FilmListItem
{
  public function __construct(?int $id, 
  ?string $name = null, 
  int $releaseYear = null, 
  ?string $poster = null, 
  ?string $description = null, 
  ?string $rating = null, 
  array $assessments = [],
  ?string $slug = null,
  ?string $internationalName = null
  )
  {
    $this->id = $id;
    $this->name = $name;
    $this->releaseYear = $releaseYear;
    $this->poster = $poster;
    $this->description = $description;
    $this->rating = $rating;
    $this->assessments = $assessments;
    $this->slug = $slug;
    $this->internationalName = $internationalName;
  }
  #[OA\Property(example: 1)]
  public ?int $id;
  #[OA\Property(example: 'Star Wars')]
  public ?string $name;

  public ?array $gallery = [];
  #[OA\Property(example: 1997)]
  public int $releaseYear;
  
  #[OA\Property(example: 'poster.jpg')]
  public ?string $poster = '';

  #[OA\Property(example: 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.')]
  public string $description = '';

  #[OA\Property(example: '4')]
  public string $rating = '';

  public array $assessments = [];

  public ?string $slug = '';

  public ?string $internationalName = '';

  public function getId(): int
  {
    return $this->id;
  }
  public function setId(int $id): static
  {
    $this->id = $id;

    return $this;
  }
  public function getName(): string
  {
    return $this->name;
  }
  public function setName(string $name): static
  {
    $this->name = $name;

    return $this;
  }

  public function getGallery(): array
  {
    return $this->gallery;
  }

  public function setGallery(array $gallery): static
  {
    $this->gallery = $gallery;

    return $this;
  }

  public function getReleaseYear(): int
  {
    return $this->releaseYear;
  }

  public function setReleaseYear(int $releaseYear): static
  {
    $this->releaseYear = $releaseYear;

    return $this;
  }

  public function getPoster(): ?string
  {
    return $this->poster;
  }

  public function setPoster(?string $poster): static
  {
    $this->poster = $poster;

    return $this;
  }

  public function getDescription(): string
  {
    return $this->description;
  }

  public function setDescription(string $description): static
  {
    $this->description = $description;

    return $this;
  }

  public function getRating(): string
  {
    return $this->rating;
  }

  public function setRating(string $rating): static
  {
    $this->rating = $rating;

    return $this;
  }

  public function setAssessments(array $assessments): static
  {
    $this->assessments = $assessments;

    return $this;
  }

  public function getAssessments(): array
  {
    return $this->assessments;
  }

  public function getSlug(): ?string
  {
    return $this->slug;
  }

  public function setSlug(?string $slug): static
  {
    $this->slug = $slug;

    return $this;
  }

  public function getInternationalName(): ?string
  {
    return $this->internationalName;
  }

  public function setInternationalName(?string $internationalName): static
  {
    $this->internationalName = $internationalName;

    return $this;
  }

}