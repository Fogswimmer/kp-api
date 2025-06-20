<?php

namespace App\Model\Response\Entity\Person;

use OpenApi\Attributes as OA;

class PersonForm
{
    #[OA\Property(example: 1)]
    public ?int $id = null;
    #[OA\Property(example: 'John')]
    public ?string $firstname = null;
    #[OA\Property(example: 'Doe')]
    public ?string $lastname = null;
    #[OA\Property(example: 1)]
    public ?int $genderId = 1;
    #[OA\Property(example: '1984-01-01')]
    public ?string $birthday = null;

    #[OA\Property(example: [2, 3])]
    public array $actedInfilmIds = [];

    #[OA\Property(example: [1, 2])]
    public array $specialtyIds = [];

    #[OA\Property(example: 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.')]
    public ?string $bio = null;
    #[OA\Property(example: 'cover.jpg')]
    public ?string $cover = null;

    #[OA\Property(example: 'avatar.jpg')]
    public ?string $avatar = null;
    public ?array $photos = [];
    #[OA\Property(example: ['actor', 'director'])]
    public ?array $specialtyNames = [];
    #[OA\Property(example: 18)]
    public int $age = 0;
    #[OA\Property(example: 'John Doe')]
    public ?string $slug = null;

    #[OA\Property(example: 'John Doe')]
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
    public function getFirstname(): string
    {
        return $this->firstname;
    }
    public function setFirstname(string $firstname): static
    {
        $this->firstname = $firstname;

        return $this;
    }

    public function getLastname(): string
    {
        return $this->lastname;
    }

    public function setLastname(string $lastname): static
    {
        $this->lastname = $lastname;

        return $this;
    }

    public function getGenderId(): int
    {
        return $this->genderId;
    }
    public function setGenderId(int $genderId): static
    {
        $this->genderId = $genderId;

        return $this;
    }

    public function getBirthday(): string
    {
        return $this->birthday;
    }
    public function setBirthday(string $birthday): static
    {
        $this->birthday = $birthday;

        return $this;
    }

    public function getActedInFilmIds(): array
    {
        return $this->actedInfilmIds;
    }
    public function setActedInFilmIds(array $actedInfilmIds): static
    {
        $this->actedInfilmIds = $actedInfilmIds;

        return $this;
    }

    public function getSpecialtyIds(): array
    {
        return $this->specialtyIds;
    }
    public function setSpecialtyIds(array $specialtyIds): static
    {
        $this->specialtyIds = $specialtyIds;

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

    public function getCover(): string
    {
        return $this->cover;
    }
    public function setCover(string $cover): static
    {
        $this->cover = $cover;

        return $this;
    }

    public function getPhotos(): array
    {
        return $this->photos;
    }
    public function setPhotos(array $photos): static
    {
        $this->photos = $photos;

        return $this;
    }

    public function getAvatar(): string
    {
        return $this->avatar;
    }
    public function setAvatar(string $avatar): static
    {
        $this->avatar = $avatar;

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

    public function getAge(): int
    {
        return $this->age;
    }
    public function setAge(int $age): static
    {
        $this->age = $age;

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

}
