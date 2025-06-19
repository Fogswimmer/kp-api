<?php

namespace App\Controller;

use App\Dto\Common\RequestPasswordDto;
use App\Entity\User;
use App\Message\PasswordChangedMessage;
use App\Service\NotificationService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use SymfonyCasts\Bundle\ResetPassword\ResetPasswordHelperInterface;
use SymfonyCasts\Bundle\ResetPassword\Exception\ResetPasswordExceptionInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use App\Dto\Common\LocaleDto;

#[Route('/api/reset-password')]
class ResetPasswordController extends AbstractController
{
    public function __construct(
        private ResetPasswordHelperInterface $resetPasswordHelper,
        private EntityManagerInterface $entityManager,
        private TranslatorInterface $translator,
        private NotificationService $notificationService,
        private MessageBusInterface $bus,
        #[Autowire('%password_reset_url%')] private string $passwordResetUrl,
    ) {
    }

    #[Route('/request', name: 'app_reset_password_request', methods: ['POST'])]
    public function requestResetPassword(
        #[MapRequestPayload()] ?RequestPasswordDto $dto,
        #[MapQueryString] LocaleDto $localeDto
    ): Response {
        $email = $dto->email;

        if (!$email) {
            return $this->json(['error' => 'Email not found'], 400);
        }

        $user = $this->entityManager->getRepository(User::class)->findOneBy(['email' => $email]);
        $uname = $user->getDisplayName() ?: $user->getUsername();
        
        if (!$user) {
            return $this->json(['error' => 'User not found'], 400);
        }

        try {
            $resetToken = $this->resetPasswordHelper->generateResetToken($user);
            $resetUrl = $this->passwordResetUrl . '/' . $resetToken->getToken();

            $to = $user->getEmail();
            $subject = $this->translator->trans('subject', [], 'reset-password', $localeDto->locale);
            $text = $this->translator->trans(
                'text',
                ['%username%' => $uname, '%resetUrl%' => $resetUrl],
                'reset-password',
                $localeDto->locale
            );
            $this->notificationService->sendEmail( $to, $subject, $text);
        } catch (ResetPasswordExceptionInterface $e) {
            return $this->json(['error' => 'Could not process reset request, because ' . $e->getReason()], 400);
        }

        return $this->json(['message' => 'Reset link sent successfully.']);
    }

    #[Route('/validate/{token}', name: 'app_reset_password_verify_token', methods: ['POST'])]
    public function verifyToken(
        string $token
    ): Response {

        try {
            $user = $this->resetPasswordHelper->validateTokenAndFetchUser($token);
        } catch (ResetPasswordExceptionInterface $e) {
            return $this->json(['error' => 'Invalid or expired token'], 400);
        }
        return $user ?
            $this->json(['message' => 'Token validated successfully.']) :
            $this->json(['error' => 'User not found'], 400);
    }

    #[Route('/{token}/new-password', name: 'app_reset_password_new_password', methods: ['POST'])]
    public function newPassword(
        #[MapRequestPayload()] ?RequestPasswordDto $dto,
        #[MapQueryString] LocaleDto $localeDto,
        UserPasswordHasherInterface $passwordHasher,
        string $token,
    ): Response {
        $user = $this->resetPasswordHelper->validateTokenAndFetchUser($token);

        if (!$user) {
            return $this->json(['error' => 'User not found'], 400);
        }

        $newPassword = $dto->password;

        if (!$newPassword) {
            return $this->json(['error' => 'Password not found'], 400);
        }

        $user->setPassword($passwordHasher->hashPassword($user, $newPassword));

        $this->entityManager->flush();
        $this->resetPasswordHelper->removeResetRequest($token);

        $uname = $user->getDisplayName() ?: $user->getUsername();

        $message = new PasswordChangedMessage($uname, $user->getEmail(), $localeDto->locale);
        $this->bus->dispatch($message);

        return $this->json(['message' => 'Password updated successfully']);
    }
}
