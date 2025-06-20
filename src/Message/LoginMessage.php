<?php

namespace App\Message;

class LoginMessage {
    private string $username;
    private string $ip;
    private string $email;
    private string $locale;
    public function __construct(
        $username,
        $ip,
        $locale,
        $email
    ) {
        $this->username = $username;
        $this->ip = $ip;
        $this->locale = $locale;
        $this->email = $email;
    }

    public function getUserName(): string {
        return $this->username;
    }

    public function getIp(): string {
        return $this->ip;
    }

    public function getLocale(): string {
        return $this->locale;
    }

    public function getEmail(): string {
        return $this->email;
    }
}