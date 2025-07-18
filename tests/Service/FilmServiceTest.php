<?php

namespace App\Tests\Service;

use App\Entity\Film;
use App\EntityListener\FilmListener;
use App\Exception\NotFound\FilmNotFoundException;
use App\Mapper\Entity\FilmMapper;
use App\Mapper\Entity\PersonMapper;
use App\Model\Response\Entity\Film\FilmList;
use App\Model\Response\Entity\Film\FilmListItem;
use App\Repository\ActorRoleRepository;
use App\Repository\AssessmentRepository;
use App\Repository\FilmRepository;
use App\Repository\PersonRepository;
use App\Repository\UserRepository;
use App\Service\Entity\FilmService;
use App\Service\FileSystemService;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class FilmServiceTest extends KernelTestCase
{
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
        );
    }

    public function testSimilarGenresRequest(): void
    {
        $slug = 'titanic';
        $count = 3;

        $targetFilm = new Film();
        $targetFilm->setId(1);
        $targetFilm->setSlug($slug);
        $targetFilm->setName('Test Film');
        $targetFilm->setGenres([1, 2, 3]);

        $similarFilmsData = [
            [
                'id' => 1,
                'title' => 'Titanic',
                'genres' => [1, 2, 4],
                'slug' => 'titanic',
                'common_genres_count' => 2,
                'similarity_score' => 0.667,
            ],
            [
                'id' => 2,
                'title' => 'Star Wars',
                'genres' => [2, 3, 5],
                'slug' => 'star-wars',
                'common_genres_count' => 2,
                'similarity_score' => 0.500,
            ],
            [
                'id' => 3,
                'title' => 'Dark Knight',
                'genres' => [1, 6, 7],
                'slug' => 'dark-knight',
                'common_genres_count' => 1,
                'similarity_score' => 0.200,
            ],
        ];

        $filmListItems = [
            new FilmListItem(1,
                'Titanic',
                1997,
                '',
                'Lorem ipsum',
                3.2,
                [],
                'titanic'
            ),
            new FilmListItem(2,
                'Star Wars',
                1997,
                '',
                'Lorem ipsum',
                3.2,
                [],
                'star-wars'
            ),
            new FilmListItem(3,
                'Dark Knight',
                2008,
                '',
                'Lorem ipsum',
                4.2,
                [],
                'dark-knight'
            ),
        ];

        $similarFilms = [];

        foreach ($similarFilmsData as $filmData) {
            $film = new Film();
            $film->setId($filmData['id']);
            $film->setName($filmData['title']);
            $film->setGenres($filmData['genres']);
            $film->setSlug($filmData['slug']);

            $similarFilms[] = $film;
        }

        $this->repositoryMock
            ->method('findBySlug')
            ->willReturn($targetFilm);

        $this->repositoryMock
            ->expects($this->once())
            ->method('findWithSimilarGenres')
            ->with(1, $count)
            ->willReturn($similarFilms);

        $this->mapperMock
            ->expects($this->exactly(3))
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

        $this->assertEquals(1, $items[0]->getId());
        $this->assertEquals('Titanic', $items[0]->getName());
        $this->assertEquals('titanic', $items[0]->getSlug());

        $this->assertEquals(2, $items[1]->getId());
        $this->assertEquals('Star Wars', $items[1]->getName());

        $this->assertEquals(3, $items[2]->getId());
        $this->assertEquals('Dark Knight', $items[2]->getName());
    }

    public function testSimilarGenresThrowsExceptionWhenFilmNotFound(): void
    {
        $slug = 'non-existent-slug';
        $count = 3;

        $this->repositoryMock
            ->expects($this->once())
            ->method('findBySlug')
            ->with($slug)
            ->willReturn(null);

        $this->expectException(FilmNotFoundException::class);
        $this->filmService->similarGenres($slug, $count);
    }

    public function testSimilarGenresWithEmptyResult(): void
    {
        $slug = 'unique-film-slug';
        $count = 5;

        $targetFilm = new Film();
        $targetFilm->setId(1);
        $targetFilm->setSlug($slug);
        $targetFilm->setName('Unique Film');
        $targetFilm->setGenres([99, 100]);

        $this->repositoryMock
            ->expects($this->once())
            ->method('findBySlug')
            ->with($slug)
            ->willReturn($targetFilm);

        $this->repositoryMock
            ->expects($this->once())
            ->method('findWithSimilarGenres')
            ->with(1, $count)
            ->willReturn([]);

        $this->mapperMock
            ->expects($this->never())
            ->method('mapToListItem');

        $result = $this->filmService->similarGenres($slug, $count);

        $this->assertInstanceOf(FilmList::class, $result);
        $this->assertCount(0, $result->getItems());
    }

    public function testSimilarGenresWithNonExistentFilm(): void
    {
        $slug = 'non-existent-film';
        $count = 5;

        $this->repositoryMock
            ->expects($this->once())
            ->method('findBySlug')
            ->with($slug)
            ->willReturn(null);

        $this->repositoryMock
            ->expects($this->never())
            ->method('findWithSimilarGenres');

        $this->expectException(FilmNotFoundException::class);
        $this->filmService->similarGenres($slug, $count);
    }
}
