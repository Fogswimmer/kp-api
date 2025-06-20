<?php

namespace App\Service;

use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mailer\MailerInterface;
class NotificationService
{
    public function __construct(
        #[Autowire('%app_domain%')] private string $appDomain,
        private MailerInterface $mailer,
    ) {}

    public function sendEmail(string $emailAddress, string $subject, string $text)
    {
        $from = 'noreply@' . parse_url($this->appDomain, PHP_URL_HOST);
        $email = (new Email())
            ->from($from)
            ->to($emailAddress)
            ->subject($subject)
            ->text($text);

        $this->mailer->send($email);
    }
}
