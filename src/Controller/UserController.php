<?php

namespace App\Controller;

use App\Dto\Entity\User\UserDto;
use App\Entity\User;
use App\Mapper\Entity\UserMapper;
use App\Model\Response\Entity\User\UserDetail;
use App\Service\Entity\UserService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

class UserController extends AbstractController
{
    public function __construct(
        private readonly UserService $userService,
        private readonly UserMapper $userMapper
    ) {}

    #[Route('/api/current-user', name: 'api_current_user', methods: ['POST', 'GET'])]
    public function index(Request $request): Response
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
        
        return $this->json($mappedUser ?? null);
    }

    #[Route('/api/current-user/edit', name: 'api_current_user/edit', methods: ['POST', 'GET'])]
    public function edit(
        Request $request,
        #[MapRequestPayload] ?UserDto $userDto
    ): Response {
        $token = $request->headers->get('Authorization');

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
    public function register(#[MapRequestPayload] ?UserDto $userDto): Response
    {
        $status = Response::HTTP_OK;
        $data = null;

        try {
            $this->userService->register($userDto);
            $data = ['message' => 'User created'];
            $status = Response::HTTP_CREATED;
        } catch (\Exception $e) {
            $status = Response::HTTP_INTERNAL_SERVER_ERROR;
            $data = $e->getMessage();
        }

        return $this->json($data, $status);
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

    #[Route('api/users/{id}/cover', name: 'api_user_cover', methods: ['POST'])]
    public function uploadCover(int $id, Request $request): Response
    {
        $file = $request->files->get('file');
        $status = Response::HTTP_OK;
        $data = null;
        
        try {
            $user = $this->userService->uploadCover($id, $file);
            $data = $this->userMapper->mapToDetail($user, new UserDetail());
        } catch (\Exception $e) {
            $status = Response::HTTP_INTERNAL_SERVER_ERROR;
            $data = $e->getMessage();
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
