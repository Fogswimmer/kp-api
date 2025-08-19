<?php

namespace App\Service\Entity;

use App\Dto\Entity\User\UserDto;
use App\Entity\User;
use App\Mapper\Entity\UserMapper;
use App\Model\Response\Entity\User\UserDetail;
use App\Repository\UserRepository;
use App\Service\FileSystemService;
use App\Service\ImageProcessorService;
use Symfony\Component\Intl\Countries;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserService
{
    public function __construct(
        private readonly UserRepository $userRepository,
        private readonly UserPasswordHasherInterface $passwordHasher,
        private FileSystemService $fileSystemService,
        private readonly UserMapper $userMapper,
        private ImageProcessorService $imageProcessorService,
    ) {
    }

    public function login(int $id): User
    {
        $user = $this->userRepository->find($id);
        $user->setLastLogin(new \DateTime());

        $this->userRepository->store($user);

        return $user;
    }

    public function register(UserDto $userDto): User
    {
        $user = new User();
        $user
            ->setUsername($userDto->username)
            ->setRoles(['ROLE_USER'])
            ->setPassword($this
                ->passwordHasher
                ->hashPassword($user, $userDto->password));


        $user->setEmail($userDto->email)
            ->setAbout($userDto->about)
            ->setAge($userDto->age)
            ->setDisplayName($userDto->displayName);

        $this->userRepository->store($user);

        return $user;
    }

    public function uploadAvatar(User $user, $file): ?UserDetail
    {
        $dirname = $this->specifyUserAvatarsPath($user->getId());
        $currentFile = $this->fileSystemService->searchFiles($dirname, 'avatar-*')[0] ?? null;

        if (null !== $currentFile) {
            $this->fileSystemService->removeFile($currentFile);
        }

        if (
            !$this->imageProcessorService->compressUploadedFile(
                $file,
                'avatar-' . uniqid(),
                $dirname
            )
        ) {
            return null;
        }
        $fullPath = $this->fileSystemService->searchFiles($dirname, 'avatar-*')[0] ?? '';
        $shortPath = $this->fileSystemService->getShortPath($fullPath);

        if (file_exists($fullPath)) {
            $user->setAvatar($shortPath);
            $this->userRepository->store($user);
        }

        return $this->userMapper->mapToDetail($user, new UserDetail());
    }

    public function get(int $id): User
    {
        return $this->findForm($id);
    }

    public function edit(User $user, UserDto $dto): UserDetail
    {
        $user
            ->setAbout($dto->about)
            ->setAge($dto->age)
            ->setEmail($dto->email)
            ->setDisplayName($dto->displayName);

        $this->userRepository->store($user);

        return $this->userMapper->mapToDetail($user, new UserDetail());
    }

    public function findForm(int $id): User
    {
        $user = $this->userRepository->find($id);

        return $user;
    }

    public function getCountryByIp(string $ip, $locale): string
    {
        $details = json_decode(file_get_contents("http://ipinfo.io/{$ip}"));

        $countryCode = $details->country;

        return Countries::getName($countryCode, $locale);
    }

    private function specifyUserAvatarsPath(int $id): string
    {
        $subDirByIdPath = $this->createUploadsDir($id);

        $avatarDirPath = $subDirByIdPath . DIRECTORY_SEPARATOR;
        $this->fileSystemService->createDir($avatarDirPath);

        return $avatarDirPath;
    }

    private function createUploadsDir(int $id): string
    {
        $userMainUploadsDir = $this->fileSystemService->getUploadsDirname('user');

        $stringId = strval($id);
        $subDirByIdPath = $userMainUploadsDir . DIRECTORY_SEPARATOR . $stringId . DIRECTORY_SEPARATOR . 'avatar';

        $this->fileSystemService->createDir($subDirByIdPath);

        return $subDirByIdPath;
    }
}
