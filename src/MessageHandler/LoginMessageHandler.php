<?php

namespace App\MessageHandler;

use App\Message\LoginMessage;
use App\Service\NotificationService;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Contracts\Translation\TranslatorInterface;

#[AsMessageHandler]
class LoginMessageHandler
{
    public function __construct(
        private TranslatorInterface $translator,
        private NotificationService $notificationService
    ) {
    }

    public function __invoke(LoginMessage $message)
    {
        $subject = $this->translator->trans('subject_login', [], 'auth', $message->getLocale());
        $text = $this->translator->trans(
            'text_login',
            ['%username%' => $message->getUsername(), '%ip%' => $message->getIp(), '%country%' => $message->getCountry()],
            'auth',
            $message->getLocale()
        );

        $this->notificationService->sendEmail($message->getEmail(), $subject, $text);
    }
}
