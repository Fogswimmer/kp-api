<?php

namespace App\Controller;

use App\Dto\Common\RequestPasswordDto;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use SymfonyCasts\Bundle\ResetPassword\ResetPasswordHelperInterface;
use SymfonyCasts\Bundle\ResetPassword\Exception\ResetPasswordExceptionInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

#[Route('/api/reset-password')]
class ResetPasswordController  extends AbstractController
{
    public function __construct(
        private ResetPasswordHelperInterface $resetPasswordHelper,
        private EntityManagerInterface $entityManager
    ) {}

    #[Route('/request', name: 'app_reset_password_request', methods: ['POST'])]
    public function requestResetPassword(
        MailerInterface $mailer,
        #[MapRequestPayload()] ?RequestPasswordDto $dto
    ): Response {
        $email = $dto->email;

        if (!$email) {
            return $this->json(['error' => 'Email not found'], 400);
        }

        $user = $this->entityManager->getRepository(User::class)->findOneBy(['email' =>  $email]);

        if (!$user) {
            return $this->json(['error' => 'User not found'], 400);
        }

        try {
            $resetToken = $this->resetPasswordHelper->generateResetToken($user);
        } catch (ResetPasswordExceptionInterface $e) {
            return $this->json(['error' => 'Could not process reset request, because ' . $e->getReason()], 400);
        }

        $resetUrl = "http://localhost:3000/password-reset/" . $resetToken->getToken();
        $email = (new Email())
            ->from('no-reply@example.com')
            ->to($user->getEmail())
            ->subject('Password Reset Request')
            ->text("Use this token to reset your password: " . $resetUrl);

        $mailer->send($email);

        return  $this->json(['message' => 'Reset link sent successfully.']);
    }

    #[Route('/reset/{token}', name: 'app_reset_password_verify_token', methods: ['POST'])]
    public function verifyToken(
        string $token
    ): Response {

        try {
            $user = $this->resetPasswordHelper->validateTokenAndFetchUser($token);
        } catch (ResetPasswordExceptionInterface $e) {
            return  $this->json(['error' => 'Invalid or expired token'], 400);
        }
        return $user ?
            $this->json(['message' => 'Token validated successfully.']) :
            $this->json(['error' => 'User not found'], 400);
    }

    #[Route('/reset/new-password/{token}', name: 'app_reset_password_new_password', methods: ['POST'])]
    public function newPassword(
        #[MapRequestPayload()] ?RequestPasswordDto $dto,
        UserPasswordHasherInterface $passwordHasher,
        string $token
    ): Response {
        $user = $this->resetPasswordHelper->validateTokenAndFetchUser($token);

        if (!$user) {
            return $this->json(['error' => 'User not found'], 400);
        }

        $newPassword = $dto->password;

        if (!$newPassword) {
            return $this->json(['error' => 'Password not found'], 400);
        }

        $user->setPassword($passwordHasher->hashPassword($user,  $newPassword));

        $this->entityManager->flush();
        $this->resetPasswordHelper->removeResetRequest($token);

        return  $this->json(['message' => 'Password updated successfully.']);
    }
}
