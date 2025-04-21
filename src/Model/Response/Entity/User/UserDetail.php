<?php

namespace App\Model\Response\Entity\User;

class UserDetail
{
  public ?int $id;

  public ?string $username;

  public ?string $displayName;

  public ?string $email;

  public ?int $age;

  public ?string $about;

  public ?string $avatar;

  public ?string $cover;

  public ?string $lastLogin;

  public ?array $assessmentsData = [];

  public ?array $roles = [];



  public function getId(): int
  {
    return $this->id;
  }

  public function setId(int $id): static
  {
    $this->id = $id;

    return $this;
  }

  public function getUsername(): ?string
  {
    return $this->username;
  }

  public function setUsername(?string $username): static
  {
    $this->username = $username;

    return $this;
  }

  public function getEmail(): ?string
  {
    return $this->email;
  }

  public function setEmail(?string $email): static
  {
    $this->email = $email;  

    return $this;
  }

  public function getAge(): ?int
  {
    return $this->age;
  }

  public function setAge(?int $age): static
  {
    $this->age = $age;

    return $this;
  }

  public function getAbout(): ?string
  {
    return $this->about;
  }

  public function setAbout(?string $about): static
  {
    $this->about = $about;

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

  public function getCover(): ?string
  {
    return $this->cover;
  }

  public function setCover(?string $cover): static
  {
    $this->cover = $cover;

    return $this;
  }

  public function getDisplayName(): ?string
  {
    return $this->displayName;
  }

  public function setDisplayName(?string $displayName): static
  {
    $this->displayName = $displayName;

    return $this;  
  }

  public function getLastLogin(): ?string
  {
    return $this->lastLogin;
  }

  public function setLastLogin(?string $lastLogin): static
  {
    $this->lastLogin = $lastLogin;

    return $this;
  }

  public function getAssessmentsData(): ?array
  {
    return $this->assessmentsData;
  }

  public function setAssessmentsData(?array $assessmentsData): static
  {
    $this->assessmentsData = $assessmentsData;

    return $this;
  }

  public function getRoles(): ?array
  {
    return $this->roles;
  }

  public function setRoles(?array $roles): static
  {
    $this->roles = $roles;

    return $this;
  }

}