<?php

namespace App\Message;

use Symfony\Component\Messenger\Attribute\AsMessage;

#[AsMessage('async')]
class EmailVerificationMessage
{
    private string $username;
    private string $email;
    private string $locale;
    private int $userId;
    private string $verifyEmailRouteName;

    public function __construct(
        $username,
        $locale,
        $email,
        $userId,
        $verifyEmailRouteName
    ) {
        $this->username = $username;
        $this->locale = $locale;
        $this->email = $email;
        $this->userId = $userId;
        $this->verifyEmailRouteName = $verifyEmailRouteName;
    }

    public function getUserName(): string
    {
        return $this->username;
    }


    public function getLocale(): string
    {
        return $this->locale;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getUserId(): int
    {
        return $this->userId;
    }

    public function setUserId(int $userId): void
    {
        $this->userId = $userId;
    }


    public function getVerifyEmailRouteName(): string
    {
        return $this->verifyEmailRouteName;
    }

    public function setVerifyEmailRouteName(string $verifyEmailRouteName): static
    {
        $this->verifyEmailRouteName = $verifyEmailRouteName;

        return $this;
    }
}
