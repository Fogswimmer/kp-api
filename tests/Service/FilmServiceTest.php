<?php

namespace App\Tests\Service;

use App\Dto\Entity\Film\FilmDto;
use App\Entity\Film;
use App\EntityListener\FilmListener;
use App\Factory\FilmFactory;
use App\Factory\UserFactory;
use App\Mapper\Entity\FilmMapper;
use App\Mapper\Entity\PersonMapper;
use App\Model\Response\Entity\Film\FilmForm;
use App\Model\Response\Entity\Film\FilmList;
use App\Repository\ActorRoleRepository;
use App\Repository\AssessmentRepository;
use App\Repository\FilmRepository;
use App\Repository\PersonRepository;
use App\Repository\UserRepository;
use App\Service\Entity\FilmService;
use App\Service\FileSystemService;
use App\Service\ImageProcessorService;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Foundry\Test\ResetDatabase;

class FilmServiceTest extends KernelTestCase
{
    use ResetDatabase;
    use Factories;
    private FilmService $filmService;
    private FilmRepository|MockObject $repositoryMock;
    private FilmMapper|MockObject $mapperMock;

    private UserRepository|MockObject $userRepositoryMock;

    private PersonRepository|MockObject $personRepositoryMock;

    private AssessmentRepository|MockObject $assessmentRepositoryMock;

    private PersonMapper|MockObject $personMapperMock;

    private FileSystemService|MockObject $fileSystemServiceMock;

    private ActorRoleRepository|MockObject $actorRoleRepositoryMock;

    private FilmListener|MockObject $filmListenerMock;

    private ImageProcessorService|MockObject $imageProcessorServiceMock;

    protected function setUp(): void
    {
        parent::setUp();

        $this->repositoryMock = $this->createMock(FilmRepository::class);
        $this->mapperMock = $this->createMock(FilmMapper::class);
        $this->assessmentRepositoryMock = $this->createMock(AssessmentRepository::class);
        $this->userRepositoryMock = $this->createMock(UserRepository::class);
        $this->personRepositoryMock = $this->createMock(PersonRepository::class);
        $this->personMapperMock = $this->createMock(PersonMapper::class);
        $this->fileSystemServiceMock = $this->createMock(FileSystemService::class);
        $this->actorRoleRepositoryMock = $this->createMock(ActorRoleRepository::class);
        $this->filmListenerMock = $this->createMock(FilmListener::class);
        $this->imageProcessorServiceMock = $this->createMock(ImageProcessorService::class);

        $this->filmService = new FilmService(
            $this->repositoryMock,
            $this->mapperMock,
            $this->assessmentRepositoryMock,
            $this->userRepositoryMock,
            $this->personRepositoryMock,
            $this->personMapperMock,
            $this->fileSystemServiceMock,
            $this->actorRoleRepositoryMock,
            $this->filmListenerMock,
            $this->imageProcessorServiceMock
        );
    }

    public function testSimilarGenresRequest(): void
    {
        $count = 3;

        $targetFilm = FilmFactory::createOne();

        $films = FilmFactory::createMany(3);

        $rawFilms = array_map(
            fn (Film $film) => $film->toArray(),
            $films
        );

        $filmListItems = array_map(
            fn (Film $film) => $this->mapperMock->mapToListItem($film),
            $films
        );

        $slug = $targetFilm->getSlug();

        $this->repositoryMock
            ->method('findBySlug')
            ->with($slug)
            ->willReturn($targetFilm);

        $this->repositoryMock
            ->expects($this->once())
            ->method('findWithSimilarGenres')
            ->with($targetFilm->getId(), $count)
            ->willReturn($rawFilms);

        $this->repositoryMock
            ->method('findBy')
            ->with(['id' => array_column($rawFilms, 'id')])
            ->willReturn($films);

        $this->mapperMock
            ->expects($this->exactly($count))
            ->method('mapToListItem')
            ->willReturnOnConsecutiveCalls(
                $filmListItems[0],
                $filmListItems[1],
                $filmListItems[2]
            );

        $result = $this->filmService->similarGenres($slug, $count);

        $this->assertInstanceOf(FilmList::class, $result);
        $this->assertCount(3, $result->getItems());

        $items = $result->getItems();

        $this->assertEquals($filmListItems[0], $items[0]);
        $this->assertEquals($filmListItems[1], $items[1]);
        $this->assertEquals($filmListItems[2], $items[2]);
    }

    // public function testFilmCreation(): void
    // {
    //     $film = FilmFactory::createOne();
    //     $user = UserFactory::createOne();

    //     $dto = new FilmDto(
    //         $film->getName(),
    //         $film->getInternationalName(),
    //         $film->getSlogan(),
    //         $film->getGenres(),
    //         $film->getReleaseYear(),
    //         array_map(fn ($actor) => $actor->getId(),
    //             $film->getActors()->toArray()),
    //         $film->getDirector()->getId(),
    //         $film->getWriter()->getId(),
    //         $film->getProducer()->getId(),
    //         $film->getComposer()->getId(),
    //         $film->getAge(),
    //         $film->getDescription(),
    //         $film->getDuration(),
    //         $film->getPoster(),
    //         $film->getBudget(),
    //         $film->getFees(),
    //         $film->getCountry()
    //     );

    //     $map = [];
    //     $persons = [];

    //     foreach ($film->getActors() as $actor) {
    //         $map[] = [$actor->getId(), $actor];
    //         $persons[] = $actor;
    //     }

    //     $persons[] = $film->getDirector();
    //     $persons[] = $film->getWriter();
    //     $persons[] = $film->getProducer();
    //     $persons[] = $film->getComposer();

    //     $this->personRepositoryMock
    //         ->method('find')
    //         ->willReturnMap($map);

    //     $this->personRepositoryMock
    //         ->method('store')
    //         ->willReturnOnConsecutiveCalls($persons)
    //     ;

    //     $this->mapperMock
    //         ->method('mapToDto')
    //         ->with($film)
    //         ->willReturn($dto);

    //     $this->repositoryMock
    //         ->expects($this->once())
    //         ->method('store')
    //         ->with($film);

    //     $this->userRepositoryMock
    //         ->expects($this->once())
    //         ->method('store')
    //         ->with($user);

    //     $result = $this->filmService->create($dto, $user);
    //     $this->assertInstanceOf(FilmForm::class, $result);

    //     $this->assertEquals($film->getSlug(), $result->getSlug());
    // }
}
