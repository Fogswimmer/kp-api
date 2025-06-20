<?php

namespace App\MessageHandler;


use App\Message\LoginMessage;
use App\Service\NotificationService;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class LoginMessageHandler
{
    public function __construct(
        private TranslatorInterface $translator,
        private NotificationService $notificationService
    ) {}
    public function __invoke(LoginMessage $message)
    {
        $subject = $this->translator->trans('subject', [], 'login', $message->getLocale());
        $text = $this->translator->trans(
            'text', 
        ['%username%' => $message->getUsername(), '%ip%' => $message->getIp()], 
        'login', $message->getLocale());

        $this->notificationService->sendEmail($message->getEmail(),  $subject, $text);
    }
}