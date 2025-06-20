<?php

namespace App\Message;

class PasswordChangedMessage {
    private string $username;
    private string $email;
    private string $locale;

    public function __construct(
        $username,
        $email,
        $locale,
    ) {
        $this->username = $username;
        $this->email = $email;
        $this->locale = $locale;
    }

    public function getUserName(): string {
        return $this->username;
    }

    public function getEmail(): string {
        return $this->email;
    }
    public function getLocale(): string {
        return $this->locale;
    }
}