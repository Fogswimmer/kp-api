<?php

namespace App\Controller;

use App\Dto\Common\LocaleDto;
use App\Dto\Entity\User\UserDto;
use App\Entity\User;
use App\Mapper\Entity\UserMapper;
use App\Message\EmailVerificationMessage;
use App\Message\LoginMessage;
use App\Model\Response\Entity\User\UserDetail;
use App\Security\EmailVerifier;
use App\Service\Entity\UserService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use SymfonyCasts\Bundle\VerifyEmail\Exception\VerifyEmailExceptionInterface;

class UserController extends AbstractController
{
    public function __construct(
        private readonly UserService $userService,
        private readonly UserMapper $userMapper,
        private MessageBusInterface $bus,
        private EmailVerifier $emailVerifier
    ) {
    }

    #[Route('/api/current-user', name: 'api_current_user', methods: ['POST', 'GET'])]
    public function index(Request $request, #[MapQueryString] LocaleDto $localeDto): Response
    {
        $token = $request->headers->get('Authorization');

        if (null === $token) {
            return $this->json(['error' => 'Token not found']);
        }
        /** @var ?User $user */
        $user = $this->getUser();

        if (null !== $user) {
            $user->setLastLogin(new \DateTime());
            $mappedUser = $this->userMapper->mapToDetail($user, new UserDetail());
        }

        $uname = $user->getDisplayName() ?: $user->getUsername();
        $ip = $request->getClientIp();

        $country = $this->userService->getCountryByIp($ip, $localeDto->locale);

        $message = new LoginMessage(
            $uname,
            $ip,
            $localeDto->locale,
            $user->getEmail(),
            $country
        );

        $this->bus->dispatch($message);

        return $this->json($mappedUser ?? null);
    }

    #[Route('/api/current-user/edit', name: 'api_current_user/edit', methods: ['POST', 'GET'])]
    public function edit(
        Request $request,
        #[MapRequestPayload] ?UserDto $userDto
    ): Response {
        $token = $request->headers->get('Authorization');
        $data = null;
        if (null === $token) {
            return $this->json(['error' => 'Token not found']);
        }

        $user = $this->getUser();

        if ($user !== null) {
            $data = $this->userService->edit($user, $userDto);
        }

        return $this->json($data);
    }

    #[Route('/api/register', name: 'api_register', methods: ['POST'])]
    public function register(#[MapRequestPayload] ?UserDto $userDto, LocaleDto $dto): Response
    {
        $status = Response::HTTP_OK;
        $data = null;

        try {
            $user = $this->userService->register($userDto);
            $message = new EmailVerificationMessage(
                $user->getUsername(),
                $user->getEmail(),
                $dto->locale ?? 'ru',
                $user->getId(),
                'api_verify_email'
            );
            $this->bus->dispatch($message);
            $data = ['message' => 'User created'];
            $status = Response::HTTP_CREATED;
        } catch (\Exception $e) {
            $status = Response::HTTP_INTERNAL_SERVER_ERROR;
            $data = $e->getMessage();
        }

        return $this->json($data, $status);
    }


    #[Route('api/verify/email', name: 'api_verify_email')]
    public function verifyUserEmail(Request $request): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        try {
            $this->emailVerifier->handleEmailConfirmation($request, $this->getUser());
        } catch (VerifyEmailExceptionInterface $exception) {

            return $this->redirectToRoute('api_register');
        }

        return $this->redirectToRoute('api_register');
    }

    #[Route('api/users/{id}/avatar', name: 'api_user_avatar', methods: ['POST'])]
    public function uploadAvatar(
        Request $request,
        #[CurrentUser] ?User $user,
    ): Response {
        $file = $request->files->get('file');
        $status = Response::HTTP_OK;
        $data = null;

        try {
            $data = $this->userService->uploadAvatar($user, $file);
            $status = Response::HTTP_CREATED;
        } catch (\Exception $e) {
            $data = $e->getMessage();
            $status = Response::HTTP_INTERNAL_SERVER_ERROR;
        }

        return $this->json($data, $status);
    }

    #[Route('/api/logout', name: 'api_logout', methods: ['POST'])]
    public function logout(): Response
    {
        return $this->json(null, Response::HTTP_NO_CONTENT);
    }

    #[Route('/api/user/{id}', name: 'api_user', methods: ['POST', 'GET'])]
    public function find(int $id): Response
    {
        return $this->json($this->userService->get($id));
    }
}
