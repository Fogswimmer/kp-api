<?php
namespace App\Model\Response\Entity\Film;

use OpenApi\Attributes as OA;

class FilmDetail
{
  public int $id;
  #[OA\Property(example: 'Star Trek')]
  public string $name;

  #[OA\Property(example: 'Star Trek')]
  public ?string $slogan;

  #[OA\Property(example: [1, 2])]
  public array $genreIds = [];

  #[OA\Property(example: ['sci-fi', 'action'])]
  public array $genreNames = [];

  #[OA\Property(example: 1966)]
  public int $releaseYear;
  public array $actorNames = [];
  #[OA\Property(example: 'sci-fi')]
  public ?string $preview;
  #[OA\Property(example: 'A new hope is on the way!')]
  public ?string $description;
  #[OA\Property(example: 9.5)]
  public ?float $rating;

  #[OA\Property(example: 18)]
  public ?int $age;
  #[OA\Property(example: '2:25:00')]
  public ?string $duration;

  #[OA\Property(example: ['https://example.com/image.jpg'])]
  public ?array $gallery = [];

  #[OA\Property(example: 'https://example.com/image.jpg')]
  public ?string $cover = null;

  public ?array $assessments = [];
  public ?string $createdAt = null;

  public ?string $updatedAt = null;
  public ?array $actorsData = [];
  public ?array $teamData = [];
  public ?array $publisherData = [];

  public ?string $poster = null;
  public ?string $trailer = null;
  public ?string $slug = null;

  public ?string $internationalName = null;

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
  public function getGenreIds(): array
  {
    return $this->genreIds;
  }
  public function setGenreIds(array $genres): static
  {
    $this->genreIds = $genres;

    return $this;
  }

  public function getGenreNames(): array
  {
    return $this->genreNames;
  }
  public function setGenreNames(array $genres): static
  {
    $this->genreNames = $genres;

    return $this;
  }

  public function getReleaseYear(): int
  {
    return $this->releaseYear;
  }
  public function setReleaseYear(?int $releaseYear): static
  {
    $this->releaseYear = $releaseYear;

    return $this;
  }

  public function getPreview(): ?string
  {
    return $this->preview;
  }
  public function setPreview(?string $preview): static
  {
    $this->preview = $preview;

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

  public function getDescription(): ?string
  {
    return $this->description;
  }
  public function setDescription(?string $description): static
  {
    $this->description = $description;

    return $this;
  }
  public function getRating(): ?float
  {
    return $this->rating;
  }
  public function setRating(float $rating): static
  {
    $this->rating = $rating;

    return $this;
  }

  public function getAge(): ?int
  {
    return $this->age;
  }
  public function setAge(int $age): static
  {
    $this->age = $age;

    return $this;
  }

  public function getDuration(): ?string
  {
    return $this->duration;
  }
  public function setDuration(?string $duration): static
  {
    $this->duration = $duration;

    return $this;
  }

  public function getSlogan(): ?string
  {
    return $this->slogan;
  }

  public function setSlogan(?string $slogan): static
  {
    $this->slogan = $slogan;

    return $this;
  }

  public function getCover(): ?string
  {
    return $this->cover;
  }
  public function setCover(?string $cover): static
  {
    $this->cover = $cover;

    return $this;
  }


  public function getCreatedAt(): string
  {
    return $this->createdAt;
  }
  public function setCreatedAt(string $createdAt): static
  {
    $this->createdAt = $createdAt;

    return $this;
  }

  public function getUpdatedAt(): string
  {
    return $this->updatedAt;
  }
  public function setUpdatedAt(string $updatedAt): static
  {
    $this->updatedAt = $updatedAt;

    return $this;
  }

  public function getAssessments(): array
  {
    return $this->assessments;
  }
  public function setAssessments(array $assessments): static
  {
    $this->assessments = $assessments;

    return $this;
  }

  public function getPublisherData(): array
  {
    return $this->publisherData;
  }
  public function setPublisherData(array $publisherData): static
  {
    $this->publisherData = $publisherData;

    return $this;
  }

  public function getActorsData(): array
  {
    return $this->actorsData;
  }
  public function setActorsData(array $actorsData): static
  {
    $this->actorsData = $actorsData;

    return $this;
  }

  public function getTeamData(): array
  {
    return $this->teamData;
  }

  public function setTeamData(array $teamData): static
  {
    $this->teamData = $teamData;

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

  public function setTrailer(?string $trailer): static
  {
    $this->trailer = $trailer;

    return $this;
  }

  public function getTrailer(): ?string
  {
    return $this->trailer;
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