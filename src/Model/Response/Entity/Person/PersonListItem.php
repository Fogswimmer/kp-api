<?php

namespace App\Model\Response\Entity\Person;

use OpenApi\Attributes as OA;

class PersonListItem
{
    public function __construct(
        ?int $id = null,
        ?string $fullName = null,
        ?string $avatar = null,
        ?string $slug = null,
        ?string $internationalName = null,
        ?array $specialtyNames = [],
        ?string $bio = null,
        ?array $filmWorks = [],
    ) {
        $this->id = $id;
        $this->name = $fullName;
        $this->avatar = $avatar;
        $this->slug = $slug;
        $this->internationalName = $internationalName;
        $this->specialtyNames = $specialtyNames;
        $this->bio = $bio;
        $this->filmWorks = $filmWorks;
    }

    #[OA\Property(example: 1)]
    public ?int $id;
    #[OA\Property(example: 'John Doe')]
    public ?string $name;

    #[OA\Property(example: 'https://example.com/avatar.jpg')]
    public ?string $avatar;

    public ?string $slug;

    public ?string $internationalName = null;

    public ?array $specialtyNames = [];

    public ?string $bio = null;

    public ?array $filmWorks = [];

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

    public function getAvatar(): ?string
    {
        return $this->avatar;
    }

    public function setAvatar(?string $avatar): static
    {
        $this->avatar = $avatar;

        return $this;
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

    public function getSpecialtyNames(): array
    {
        return $this->specialtyNames;
    }

    public function setSpecialtyNames(array $specialtyNames): static
    {
        $this->specialtyNames = $specialtyNames;

        return $this;
    }

    public function getBio(): string
    {
        return $this->bio;
    }

    public function setBio(string $bio): static
    {
        $this->bio = $bio;

        return $this;
    }

    public function setFilmWorks(array $filmWorks): static
    {
        $this->filmWorks = $filmWorks;

        return $this;
    }

    public function getFilmWorks(): array
    {
        return $this->filmWorks;
    }
}
