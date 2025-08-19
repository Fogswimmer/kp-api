<?php

namespace App\MessageHandler;

use App\Entity\User;
use App\Message\EmailVerificationMessage;
use App\Message\LoginMessage;
use App\Service\NotificationService;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Contracts\Translation\TranslatorInterface;
use SymfonyCasts\Bundle\VerifyEmail\VerifyEmailHelperInterface;

#[AsMessageHandler]
class EmailVerificationMessageHandler
{
    public function __construct(
        private TranslatorInterface $translator,
        private NotificationService $notificationService,
        private VerifyEmailHelperInterface $verifyEmailHelper,
    ) {
    }

    public function __invoke(EmailVerificationMessage $message)
    {
        $signatureComponents = $this->verifyEmailHelper->generateSignature(
            $message->getVerifyEmailRouteName(),
            $message->getUserId(),
            $message->getEmail()
        );

        $signedUrl = $signatureComponents->getSignedUrl();
        $expiresAtMessageKey = $signatureComponents->getExpirationMessageKey();
        $expiresAtMessageData = $signatureComponents->getExpirationMessageData();

        $expiresAtMessage = $this->translator->trans(
            $expiresAtMessageKey,
            $expiresAtMessageData,
            'VerifyEmailBundle',
            $message->getLocale()
        );

        $subject = $this->translator->trans('subject_verify_email', [], 'auth', $message->getLocale());
        $text = $this->translator->trans(
            'text_verify_email',
            ['%username%' => $message->getUsername(), '%link%' => $signedUrl, '%expiresAt%' => $expiresAtMessage],
            'auth',
            $message->getLocale()
        );

        $this->notificationService->sendEmail($message->getEmail(), $subject, $text);
    }
}
