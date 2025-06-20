<?php

namespace App\Service\Entity;

use App\Dto\Entity\Film\FilmQueryDto;
use App\Dto\Entity\Assessment\AssessmentDto;
use App\Dto\Entity\Film\FilmDto;
use App\Entity\ActorRole;
use App\Entity\Assessment;
use App\Entity\Film;
use App\Entity\User;
use App\Enum\Genres;
use App\Exception\NotFound\FilmNotFoundException;
use App\Exception\NotFound\PersonNotFoundException;
use App\Mapper\Entity\FilmMapper;
use App\Mapper\Entity\PersonMapper;
use App\Model\Response\Entity\Film\FilmDetail;
use App\Model\Response\Entity\Film\FilmForm;
use App\Model\Response\Entity\Film\FilmList;
use App\Model\Response\Entity\Film\FilmPaginateList;
use App\Repository\ActorRoleRepository;
use App\Repository\AssessmentRepository;
use App\Repository\FilmRepository;
use App\Repository\PersonRepository;
use App\Repository\UserRepository;
use App\Service\FileSystemService;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use App\Exception\Denied\AccessDeniedException;
use App\Exception\NotFound\AssessmentNotFoundException;
use App\EntityListener\FilmListener;

class FilmService
{
    public function __construct(
        private FilmRepository $repository,
        private AssessmentRepository $assessmentRepository,
        private UserRepository $userRepository,
        private PersonRepository $personRepository,
        private FilmMapper $filmMapper,
        private PersonMapper $personMapper,
        private FileSystemService $fileSystemService,
        private ActorRoleRepository $actorRoleRepository,
        private FilmListener $filmListener
    ) {
    }

    public function assess(
        int $id,
        AssessmentDto $dto,
        ?string $locale,
        #[CurrentUser] User $user,
    ): FilmDetail {
        $film = $this->repository->find($id);

        if (null === $user) {
            throw new \Exception();
        }

        $assessment = new Assessment();
        $assessment
            ->setFilm($film)
            ->setAuthor($user)
            ->setRating($dto->rating);
        if ($dto->comment !== null) {
            $assessment->setComment($dto->comment);
        }

        $film->addAssessment($assessment);
        $filmAssessments = $film->getAssessments()->toArray();

        $film->setRating(
            array_sum(array_map(function (Assessment $assessment) {
                return $assessment->getRating();
            }, $filmAssessments)) / count($filmAssessments)
        );

        $this->assessmentRepository->store($assessment);
        $this->userRepository->store($assessment);
        $this->repository->store($film);

        return $this->get($film->getSlug(), $locale);
    }

    public function checkFilmsPresence(): bool
    {
        $films = $this->repository->findAll();
        foreach ($films as $film) {
            if ($film->getSlug() === null) {
                $slug = $this->filmListener->generateSlug($film);
                $film->setSlug($slug);
            }
            $this->repository->store($film);
        }
        return $this->repository->findAll() !== [];
    }

    public function get(string $slug, ?string $locale = null): FilmDetail
    {
        $film = $this->findBySlug($slug);
        $filmDetail = $this
            ->filmMapper
            ->mapToDetail($film, new FilmDetail(), $locale);
        $id = $film->getId();

        $galleryPaths = $this->setGalleryPaths($id);
        $filmDetail->setGallery($galleryPaths);

        return $filmDetail;
    }

    public function findForm(string $slug): FilmForm
    {
        $film = $this->findBySlug($slug);
        $form = $this->filmMapper->mapToForm($film, new FilmForm());

        $galleryPaths = $this->setGalleryPaths($film->getId());
        $form->setGallery($galleryPaths);

        return $form;
    }

    public function latest(int $count): FilmList
    {
        $films = $this->repository->findLatest($count);

        $items = array_map(
            fn (Film $film) => $this->filmMapper->mapToListItem($film),
            $films
        );

        foreach ($items as $item) {
            $galleryPaths = $this->setGalleryPaths($item->getId());
            $item->setGallery($galleryPaths);
        }

        return new FilmList($items);
    }

    public function top(int $count): FilmList
    {
        $films = $this->repository->findTop($count);

        $items = array_map(
            fn (Film $film) => $this->filmMapper->mapToListItem($film),
            $films
        );

        foreach ($items as $item) {
            $galleryPaths = $this->setGalleryPaths($item->getId());
            $item->setGallery($galleryPaths);
        }

        return new FilmList($items);
    }

    public function filter(FilmQueryDto $filmQueryDto): FilmPaginateList
    {
        $totalPages = 1;
        $currentPage = 1;
        $total = $this->repository->total();
        $films = $this->repository->filterByQueryParams($filmQueryDto);
        if ($filmQueryDto->limit !== 0) {
            $totalPages = intval(ceil($total / $filmQueryDto->limit));
            $currentPage = $filmQueryDto->offset / $filmQueryDto->limit + 1;
        }
        $locale = $filmQueryDto->locale ?? 'ru';
        $items = array_map(
            fn (Film $film) => $this->filmMapper->mapToDetail($film, new FilmDetail(), $locale),
            $films
        );

        foreach ($items as $item) {
            $galleryPaths = $this->setGalleryPaths($item->getId());
            $item->setGallery($galleryPaths);
        }

        return new FilmPaginateList($items, $totalPages, $currentPage);
    }

    public function create(FilmDto $dto, #[CurrentUser] User $user): FilmForm
    {
        $film = new Film();
        $genreIds = $dto->genreIds;
        $genres = [];
        foreach ($genreIds as $genreId) {
            $genres[] = Genres::matchIdAndGenre($genreId);
        }
        $film->setGenres($genres);

        $actorIds = $dto->actorIds;

        foreach ($actorIds as $actorId) {
            $actor = $this->personRepository->find($actorId);
            if (null === $actor) {
                throw new PersonNotFoundException();
            }
            $film->addActor($actor);
            $this->personRepository->store($actor);
        }

        $directorId = $dto->directorId;
        $director = $this->personRepository->find($directorId);

        if (null === $director) {
            throw new PersonNotFoundException();
        }

        $film->setDirectedBy($director);

        $producerId = $dto->producerId;
        $producer = $this->personRepository->find($producerId);

        if (null === $producer) {
            throw new PersonNotFoundException();
        }

        $film->setProducer($producer);

        $writerId = $dto->writerId;
        $writer = $this->personRepository->find($writerId);

        if (null === $writer) {
            throw new PersonNotFoundException();
        }

        $film->setWriter($writer);

        $composerId = $dto->composerId;
        $composer = $this->personRepository->find($composerId);

        if (null === $composer) {
            throw new PersonNotFoundException();
        }

        $film->setComposer($composer);
        $roleNames = $dto->roleNames ?? [];

        if (count($roleNames) !== 0) {
            foreach ($roleNames as $roleName) {
                $role = new ActorRole();
                $role->setName($roleName);
                $film->addActorRole($role);
                $this->actorRoleRepository->store($role);
            }
        }

        $film
            ->setName($dto->name)
            ->setInternationalName($dto->internationalName)
            ->setReleaseYear($dto->releaseYear)
            ->setDuration($dto->duration)
            ->setDescription($dto->description)
            ->setAge($dto->age)
            ->setSlogan($dto->slogan)
            ->setTrailer($dto->trailer)
            ->setRating(0)
            ->setPublisher($user);

        $this->repository->store($film);
        $this->userRepository->store($user);

        return $this->findForm($film->getSlug());
    }

    public function update(int $id, FilmDto $dto, string $locale): FilmDetail
    {
        $film = $this->repository->find($id);
        $actorIds = $dto->actorIds;

        $newActors = [];
        foreach ($actorIds as $actorId) {
            $newActor = $this->personRepository->find($actorId);

            if (null === $newActor) {
                throw new PersonNotFoundException();
            }

            $newActors[] = $newActor;
            $this->personRepository->store($newActor);
        }

        $film->updateActors($newActors);

        $directorId = $dto->directorId;
        $director = $this->personRepository->find($directorId);

        if (null === $director) {
            throw new PersonNotFoundException();
        }

        $film->setDirectedBy($director);
        $genreIds = $dto->genreIds;
        $genres = [];

        foreach ($genreIds as $genreId) {
            $genres[] = Genres::matchIdAndGenre($genreId);
        }

        $film->setGenres($genres);

        $producerId = $dto->producerId;
        $producer = $this->personRepository->find($producerId);

        if (null === $producer) {
            throw new PersonNotFoundException();
        }

        $film->setProducer($producer);

        $writerId = $dto->writerId;
        $writer = $this->personRepository->find($writerId);

        if (null === $writer) {
            throw new PersonNotFoundException();
        }

        $film->setWriter($writer);

        $composerId = $dto->composerId;
        $composer = $this->personRepository->find($composerId);

        if (null === $composer) {
            throw new PersonNotFoundException();
        }

        $film->setComposer($composer);

        $film
            ->setName($dto->name)
            ->setInternationalName($dto->internationalName)
            ->setReleaseYear($dto->releaseYear)
            ->setDuration($dto->duration)
            ->setDescription($dto->description)
            ->setAge($dto->age)
            ->setSlogan($dto->slogan)
            ->setPoster($dto->poster)
        ;
        if ($dto->trailer !== null) {
            $film->setTrailer($dto->trailer);
        }

        $this->repository->store($film);

        return $this->get($film->getSlug(), $locale);
    }

    public function delete(int $id): void
    {
        $film = $this->repository->find($id);
        $galleryFiles = $this->fileSystemService->searchFiles($this->specifyFilmGalleryPath($id), 'picture-*');

        foreach ($galleryFiles as $file) {
            $this->fileSystemService->removeFile($file);
        }

        $this->repository->remove($film);
    }

    public function uploadGallery(int $id, array $files): FilmForm
    {
        $film = $this->repository->find($id);
        $dirName = $this->specifyFilmGalleryPath($film->getId());
        $currentFiles = $this->fileSystemService->searchFiles($dirName, 'picture-*');

        $currentFileIndexes = [];

        foreach ($currentFiles as $file) {
            if (preg_match('/picture-(\d+)/', $file, $matches)) {
                $currentFileIndexes[] = (int) $matches[1];
            }
        }

        $maxIndex = !empty($currentFileIndexes) ? max($currentFileIndexes) : 0;

        foreach ($files as $file) {
            $maxIndex++;
            $indexedFileName = 'picture-' . $maxIndex;
            $this->fileSystemService->upload($file, $dirName, $indexedFileName);
        }

        return $this->findForm($film->getSlug());
    }

    public function deleteFromGallery(int $id, array $fileNames): FilmForm
    {
        $film = $this->repository->find($id);
        $dirName = $this->specifyFilmGalleryPath($film->getId());
        $foundPictures = [];

        foreach ($fileNames as $fileName) {
            $foundPictures[] = $this->fileSystemService->searchFiles($dirName, $fileName);
        }

        foreach ($foundPictures as $picture) {
            foreach ($picture as $file) {
                $this->fileSystemService->removeFile($file);
            }
        }
        // TODO
        if ($film->getPoster() !== null && in_array($film->getPoster(), $fileNames)) {
            $film->setPoster(null);
        }

        return $this->findForm($film->getSlug());
    }

    public function deleteAssessment(int $filmId, int $assessmentId, User $user): FilmForm
    {
        $film = $this->repository->find($filmId);
        $assessment = $this->assessmentRepository->find($assessmentId);

        if (null === $assessment) {
            throw new AssessmentNotFoundException();
        }

        if ($user->getRoles() !== ['ROLE_ADMIN', 'ROLE_USER']) {
            if ($assessment->getAuthor()->getId() !== $user->getId()) {
                throw new AccessDeniedException();
            }
        }
        $this->assessmentRepository->remove($assessment);

        $newAssessments = $film->getAssessments()->toArray();

        $film->setRating(
            array_sum(array_map(function (Assessment $assessment) {
                return $assessment->getRating();
            }, $newAssessments)) / count($newAssessments)
        );

        $this->repository->store($film);

        return $this->findForm($film->getSlug());
    }

    private function setGalleryPaths(int $id): array
    {
        $galleryDirPath = $this->specifyFilmGalleryPath($id);
        $galleryFiles = $this->fileSystemService->searchFiles($galleryDirPath);
        $shortPaths = [];

        foreach ($galleryFiles as $file) {
            $shortPaths[] = $this->fileSystemService->getShortPath($file);
        }

        return $shortPaths;
    }

    private function specifyFilmGalleryPath(int $id): string
    {
        $subDirByIdPath = $this->createUploadsDir($id);

        $galleryDirPath = $subDirByIdPath . DIRECTORY_SEPARATOR . 'gallery';
        $this->fileSystemService->createDir($galleryDirPath);

        return $galleryDirPath;
    }

    private function createUploadsDir(int $id): string
    {
        $filmBaseUploadsDir = $this->fileSystemService->getUploadsDirname('film');

        $stringId = strval($id);
        $subDirByIdPath = $filmBaseUploadsDir . DIRECTORY_SEPARATOR . $stringId;

        $this->fileSystemService->createDir($subDirByIdPath);

        return $subDirByIdPath;
    }

    private function findBySlug(string $slug): Film
    {
        $film = $this->repository->findOneBy(['slug' => $slug]);

        if (null === $film) {
            throw new FilmNotFoundException();
        }

        return $film;
    }
}
