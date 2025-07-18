<?php

namespace App\Tests\Service;

use App\Entity\User;
use App\Mapper\Entity\UserMapper;
use App\Repository\UserRepository;
use App\Service\Entity\UserService;
use App\Service\FileSystemService;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserServiceTest extends KernelTestCase
{
    public function testLoginUpdatesLastLoginAndPersistsUser(): void
    {
        $user = new User();
        $user->setLastLogin(null);

        $userRepository = $this->createMock(UserRepository::class);
        $userRepository->expects($this->once())
            ->method('find')
            ->with(1)
            ->willReturn($user);

        $userRepository->expects($this->once())
            ->method('store')
            ->with($this->callback(function (User $user) {
                return $user->getLastLogin() instanceof \DateTimeInterface;
            }));

        $passwordHasher = $this->createMock(UserPasswordHasherInterface::class);
        $fileSystem = $this->createMock(FileSystemService::class);
        $mapper = $this->createMock(UserMapper::class);

        $service = new UserService($userRepository, $passwordHasher, $fileSystem, $mapper);

        $result = $service->login(1);

        $this->assertSame($user, $result);
        $this->assertInstanceOf(\DateTimeInterface::class, $result->getLastLogin());
    }
}
