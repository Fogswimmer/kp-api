<?php

namespace App\MessageHandler;

use App\Message\PasswordChangedMessage;
use App\Service\NotificationService;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class PasswordChangedMessageHandler
{
    public function __construct(
        private TranslatorInterface $translator,
        private NotificationService $notificationService
    ) {}
    public function __invoke(PasswordChangedMessage $message)
    {
        $subject = $this->translator->trans('subject', [], 'reset-password', $message->getLocale());
        $text =  $this->translator->trans('success', 
        ['%username%' => $message->getUsername(),], 'reset-password', $message->getLocale());

        $this->notificationService->sendEmail($message->getEmail(),  $subject, $text);
    }
}
