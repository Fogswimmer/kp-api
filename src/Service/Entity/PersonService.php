<?php

namespace App\Service\Entity;

use App\Dto\Entity\Person\PersonQueryDto;
use App\Dto\Entity\Person\PersonDto;
use App\Entity\Person;
use App\Entity\User;
use App\Enum\Specialty;
use App\Exception\NotFound\PersonNotFoundException;
use App\Mapper\Entity\PersonMapper;
use App\Model\Response\Entity\Person\PersonDetail;
use App\Model\Response\Entity\Person\PersonForm;
use App\Model\Response\Entity\Person\PersonList;
use App\Model\Response\Entity\Person\PersonPaginateList;
use App\Repository\FilmRepository;
use App\Repository\PersonRepository;
use App\Repository\UserRepository;
use App\Service\FileSystemService;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Contracts\Translation\TranslatorInterface;
use App\EntityListener\PersonListener;

class PersonService
{
    public function __construct(
        private PersonRepository $repository,
        private FilmRepository $filmRepository,
        private PersonMapper $personMapper,
        private FileSystemService $fileSystemService,
        private TranslatorInterface $translator,
        private UserRepository $userRepository,
        private PersonListener $personListener
    ) {
    }

    public function get(string $slug, ?string $locale = null): PersonDetail
    {
        $person = $this->findBySlug($slug);
        $id = $person->getId();
        $personDetail = $this
            ->personMapper
            ->mapToDetail($person, new PersonDetail(), $locale);
        $photoPaths = $this->setPhotosPaths($id);
        $personDetail->setPhotos($photoPaths);
        if (!$personDetail->getCover()) {
            $personDetail->setCover($this->specifyCoverPath($id));
        }
        return $personDetail;
    }

    public function findForm(string $slug): PersonForm
    {
        $person = $this->findBySlug($slug);

        $id = $person->getId();

        $form = $this->personMapper->mapToForm($person, new PersonForm());
        $photoPaths = $this->setPhotosPaths($id);

        $form->setPhotos($photoPaths);
        $form->setCover($this->specifyCoverPath($id));

        return $form;
    }

    public function create(PersonDto $dto, #[CurrentUser] User $user): PersonForm
    {
        $person = new Person();
        $person
            ->setFirstname($dto->firstname)
            ->setLastname($dto->lastname)
            ->setInternationalName($dto->internationalName)
            ->setBirthday($dto->birthday)
            ->setGender($dto->genderId)
            ->setPublisher($user);
        if ($dto->bio !== null) {
            $person->setBio($dto->bio);
        }
        if ($dto->avatar !== null) {
            $person->setAvatar($dto->avatar);
        }
        $specialtyIds = $dto->specialtyIds;
        $specialties = [];

        foreach ($specialtyIds as $specialtyId) {
            $specialties[] = Specialty::matchIdAndSpecialty($specialtyId);
        }

        $person->setSpecialties($specialties);

        $this->repository->store($person);
        $this->userRepository->store($user);

        return $this->findForm($person->getSlug());
    }

    public function update(int $id, PersonDto $dto): PersonForm
    {
        $person = $this->repository->find($id);

        if (null === $person) {
            throw new PersonNotFoundException();
        }

        $person->setFirstname($dto->firstname);
        $person->setLastname($dto->lastname);
        $person->setBirthday($dto->birthday);
        $person->setGender($dto->genderId);
        $person->setInternationalName($dto->internationalName);

        $specialties = [];
        $specialtyIds = $dto->specialtyIds;

        foreach ($specialtyIds as $specialtyId) {
            $specialties[] = Specialty::matchIdAndSpecialty($specialtyId);
        }

        $person->setSpecialties($specialties);

        if ($dto->bio !== null) {
            $person->setBio($dto->bio);
        }
        if ($dto->avatar !== null) {
            $person->setAvatar($dto->avatar);
        }

        if ($dto->cover !== null) {
            $person->setCover($dto->cover);
        }

        $this->repository->store($person);

        return $this->findForm($person->getSlug());
    }

    public function delete(int $id): void
    {
        $person = $this->repository->find($id);
        $films = $person->getFilms();

        foreach ($films as $film) {
            $person->removeFilm($film);
        }

        $galleryFiles = $this->fileSystemService->searchFiles(
            $this->specifyPersonPhotosPath($id),
            'photo-*'
        );

        foreach ($galleryFiles as $file) {
            $this->fileSystemService->removeFile($file);
        }

        $this->repository->remove($person);
    }

    public function uploadPhotos(int $id, array $files): PersonForm
    {
        $person = $this->repository->find($id);
        $dirName = $this->specifyPersonPhotosPath($person->getId());

        $currentFiles = $this->fileSystemService->searchFiles($dirName, 'photo-*');

        $currentFileIndexes = [];

        foreach ($currentFiles as $file) {
            if (preg_match('/photo-(\d+)/', $file, $matches)) {
                $currentFileIndexes[] = (int) $matches[1];
            }
        }

        $maxIndex = !empty($currentFileIndexes) ? max($currentFileIndexes) : 0;

        foreach ($files as $file) {
            $maxIndex++;
            $indexedFileName = 'photo-' . $maxIndex;
            $this->fileSystemService->upload($file, $dirName, $indexedFileName);
        }

        return $this->findForm($person->getSlug());
    }

    public function uploadCover(int $id, $file): PersonForm
    {
        $person = $this->repository->find($id);
        $dirName = $this->specifyPersonPhotosPath($person->getId());
        $currentFile = $this->fileSystemService->searchFiles($dirName, 'cover')[0] ?? null;

        if (null !== $currentFile) {
            $this->fileSystemService->removeFile($currentFile);
        }

        $this->fileSystemService->upload($file, $dirName, 'cover');

        $fullPath = $this->fileSystemService->searchFiles($dirName, 'cover')[0] ?? '';
        $shortPath = $this->fileSystemService->getShortPath($fullPath);

        if (file_exists($fullPath)) {
            $person->setCover($shortPath);
            $this->repository->store($person);
        }

        return $this->findForm($person->getSlug());
    }


    public function deletePhotos(int $id, array $fileNames): PersonForm
    {
        $person = $this->repository->find($id);
        $dirName = $this->specifyPersonPhotosPath($person->getId());
        $foundPictures = [];

        foreach ($fileNames as $fileName) {
            $foundPictures[] = $this->fileSystemService->searchFiles($dirName, $fileName);
        }

        foreach ($foundPictures as $picture) {
            foreach ($picture as $file) {
                $this->fileSystemService->removeFile($file);
            }
        }

        return $this->findForm($person->getSlug());
    }
    public function listSpecialistsBySpecialty(Specialty $specialty): array
    {
        $persons = $this->repository->findAll();
        $specialists = [];
        foreach ($persons as $person) {
            $specialties = array_map(
                fn (int $specialty) => Specialty::tryFrom($specialty),
                $person->getSpecialties()
            );
            foreach ($specialties as $specialtyItem) {
                if ($specialtyItem::matchSpecialty($specialty)) {
                    $specialists[] = $person;
                    break;
                }
            }
        }

        return $specialists;
    }

    public function listSpecialists(): array
    {
        $specialists = [
            'actors' => $this->listSpecialistsBySpecialty(Specialty::ACTOR),
            'directors' => $this->listSpecialistsBySpecialty(Specialty::DIRECTOR),
            'producers' => $this->listSpecialistsBySpecialty(Specialty::PRODUCER),
            'writers' => $this->listSpecialistsBySpecialty(Specialty::WRITER),
            'composers' => $this->listSpecialistsBySpecialty(specialty: Specialty::COMPOSER)
        ];

        return array_map(
            fn (array $persons) => $this->personMapper->mapToEntityList($persons),
            $specialists
        );
    }

    public function listPopularActors(string $locale): PersonList
    {
        $actors = $this->listSpecialistsBySpecialty(Specialty::ACTOR);
        $popularActors = [];
        foreach ($actors as $actor) {
            if (count($actor->getFilms()) > 2) {
                $popularActors[] = $actor;
            }
        }

        return $this->personMapper->mapToEntityList($popularActors, $locale);
    }

    public function filter(PersonQueryDto $personQueryDto): PersonPaginateList
    {
        $persons = $this->repository->filterByQueryParams($personQueryDto);
        $total = $this->repository->total();
        $totalPages = 1;
        $currentPage = 1;
        $locale = $personQueryDto->locale ?? 'ru';
        if ($personQueryDto->limit !== 0) {
            $totalPages = intval(ceil($total / $personQueryDto->limit));
            $currentPage = $personQueryDto->offset / $personQueryDto->limit + 1;
        }
        $items = array_map(
            fn (Person $person) => $this->personMapper->mapToDetail($person, new PersonDetail(), $locale),
            $persons
        );
        foreach ($items as $item) {
            $photosPaths = $this->setPhotosPaths($item->getId());
            $item->setPhotos($photosPaths);
        }

        return new PersonPaginateList($items, $totalPages, $currentPage);
    }

    public function checkPersonsPresence(): bool
    {
        $persons = $this->repository->findAll();
        foreach ($persons as $person) {
            if ($person->getSlug() === null) {
                $slug = $this->personListener->generateSlug($person);
                $person->setSlug($slug);
            }
            $this->repository->store($person);
        }

        return $this->repository->findAll() !== [];
    }

    private function setPhotosPaths(int $id): array
    {
        $photosDirPath = $this->specifyPersonPhotosPath($id);

        $photoFiles = $this->fileSystemService->searchFiles($photosDirPath, 'photo-*');
        $shortPaths = [];

        foreach ($photoFiles as $file) {
            $shortPaths[] = $this->fileSystemService->getShortPath($file);
        }

        return $shortPaths;
    }

    private function specifyPersonPhotosPath(int $id): string
    {
        $subDirByIdPath = $this->createUploadsDir($id);

        $photosDirPath = $subDirByIdPath . DIRECTORY_SEPARATOR . 'photos';
        $this->fileSystemService->createDir($photosDirPath);

        return $photosDirPath;
    }

    private function specifyCoverPath(int $id): string
    {
        $person = $this->repository->find($id);
        $dirName = $this->specifyPersonPhotosPath($person->getId());

        $files = $this->fileSystemService->searchFiles($dirName, 'cover');

        return $this->fileSystemService->getShortPath($files[0] ?? '');
    }

    private function createUploadsDir(int $id): string
    {
        $personBaseUploadsDir = $this->fileSystemService->getUploadsDirname('person');

        $stringId = strval($id);
        $subDirByIdPath = $personBaseUploadsDir . DIRECTORY_SEPARATOR . $stringId;

        $this->fileSystemService->createDir($subDirByIdPath);

        return $subDirByIdPath;
    }

    private function findBySlug(string $slug): Person
    {
        $person = $this->repository->findOneBy(['slug' => $slug]);

        if (null === $person) {
            throw new PersonNotFoundException();
        }

        return $person;
    }
}
