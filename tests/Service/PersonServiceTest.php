<?php

namespace App\Tests\Service;

use App\Entity\Person;
use App\EntityListener\PersonListener;
use App\Factory\PersonFactory;
use App\Mapper\Entity\PersonMapper;
use App\Model\Response\Entity\Person\PersonList;
use App\Repository\PersonRepository;
use App\Repository\UserRepository;
use App\Service\Entity\PersonService;
use App\Service\FileSystemService;
use App\Service\ImageProcessorService;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Foundry\Test\ResetDatabase;

class PersonServiceTest extends KernelTestCase
{
    use ResetDatabase;
    use Factories;

    private PersonService $personService;
    private PersonRepository|MockObject $repositoryMock;
    private PersonMapper|MockObject $personMapperMock;
    private FileSystemService|MockObject $fileSystemServiceMock;
    private UserRepository|MockObject $userRepositoryMock;
    private PersonListener|MockObject $personListenerMock;
    private ImageProcessorService|MockObject $imageProcessorService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->repositoryMock = $this->createMock(PersonRepository::class);

        $this->personMapperMock = $this->createMock(PersonMapper::class);
        $this->fileSystemServiceMock = $this->createMock(FileSystemService::class);
        $this->userRepositoryMock = $this->createMock(UserRepository::class);
        $this->personListenerMock = $this->createMock(PersonListener::class);
        $this->imageProcessorService = $this->createMock(ImageProcessorService::class);

        $this->personService = new PersonService(
            $this->repositoryMock,

            $this->personMapperMock,
            $this->fileSystemServiceMock,
            $this->userRepositoryMock,
            $this->personListenerMock,
            $this->imageProcessorService
        );
    }

        public function testSimilarSpecialtiesRequest(): void
    {
        $count = 3;

        $targetPerson = PersonFactory::createOne();

        $persons = PersonFactory::createMany(3);

        $rawPersons = array_map(
            fn(Person $person) => $person->toArray(),
            $persons
        );

        $slug = $targetPerson->getSlug();

        $this->repositoryMock
            ->method('findBySlug')
            ->with($slug)
            ->willReturn($targetPerson);

        $this->repositoryMock
            ->expects($this->once())
            ->method('findWithSimilarSpecialties')
            ->with($targetPerson->getId(), $count)
            ->willReturn($rawPersons);

        $this->repositoryMock
            ->method('findBy')
            ->with(['id' => array_column($rawPersons, 'id')])
            ->willReturn($persons);

        $result = $this->personService->similarSpecialties($slug, $count);

        $this->assertInstanceOf(PersonList::class, $result);
        $this->assertCount(3, $result->getItems());
    }

    public function testCheckPersonsPresence(): void
    {
        $persons = PersonFactory::createMany(3);

        $this->repositoryMock
            ->method('findAll')
            ->willReturn($persons)
        ;

        $result = $this->personService->checkPersonsPresence();

        $this->assertTrue($result);
    }
}
