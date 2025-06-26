<?php

namespace App\Service;

use App\Exception\NotFound\FileNotFoundException;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\String\Slugger\SluggerInterface;

class FileSystemService
{
    public function __construct(
        private SluggerInterface $slugger,
        #[Autowire('%person_uploads%')] private string $personPhotoDir,
        #[Autowire('%uploads_dir%')] private string $defaultUploadsDir,
        #[Autowire('%public_dir%')] private string $publicDir,
        #[Autowire('%film_uploads%')] private string $filmUploadsDir,
        #[Autowire('%user_uploads%')] private string $userAvatarDir,
        #[Autowire('%app_url%')] private string $appUrl,
    ) {
    }

    public function upload(UploadedFile $file, string $path, ?string $customFileName = null): string
    {
        $originalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $safeFilename = $this->slugger->slug($originalFilename);

        $customFileName ?
            ($fileName = $customFileName.'.'.$file->guessExtension())
            :
            ($fileName = $safeFilename.'-'.uniqid().'.'.$file->guessExtension());
        try {
            $file->move($path, $fileName);
        } catch (FileException $e) {
            throw new FileNotFoundException();
        }

        return $fileName;
    }

    public function getShortPath(string $path): string
    {
        $path = $this->appUrl.str_replace($this->publicDir, '', $path);
        dd($path);

        return $path;
    }

    public function getPublicDir(): string
    {
        return $this->publicDir;
    }

    public function getUploadsDirname(string $entityName): string
    {
        match ($entityName) {
            'person' => $dirname = $this->personPhotoDir,
            'film' => $dirname = $this->filmUploadsDir,
            'user' => $dirname = $this->userAvatarDir,
            default => $dirname = $this->defaultUploadsDir,
        };

        return $dirname;
    }

    public function createDir(string $path): string
    {
        if (!is_dir($path)) {
            mkdir($path, 0775, true);
        }

        return $path;
    }

    public function searchFiles(string $dirName, ?string $fileName = '*'): array
    {
        $files = glob($dirName.DIRECTORY_SEPARATOR."$fileName.*");

        return $files;
    }

    public function removeFile(?string $path): void
    {
        if (file_exists($path)) {
            unlink($path);
        }
    }
}
