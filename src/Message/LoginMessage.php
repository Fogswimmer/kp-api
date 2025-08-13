<?php

namespace App\Message;

use Symfony\Component\Messenger\Attribute\AsMessage;

#[AsMessage('async')]
class LoginMessage
{
    private string $username;
    private string $ip;
    private string $email;
    private string $locale;

    private string $country;

    public function __construct(
        $username,
        $ip,
        $locale,
        $email,
        $country,
    ) {
        $this->username = $username;
        $this->ip = $ip;
        $this->locale = $locale;
        $this->email = $email;
        $this->country = $country;
    }

    public function getUserName(): string
    {
        return $this->username;
    }

    public function getIp(): string
    {
        return $this->ip;
    }

    public function getLocale(): string
    {
        return $this->locale;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getCountry(): string
    {
        return $this->country;
    }
}
