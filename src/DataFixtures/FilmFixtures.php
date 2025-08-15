<?php

namespace App\DataFixtures;

use App\Entity\Assessment;
use App\Entity\Film;
use App\Enum\Genres;
use App\Enum\Specialty;
use App\Repository\PersonRepository;
use App\Repository\UserRepository;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class FilmFixtures extends Fixture implements DependentFixtureInterface
{
    public function __construct(
        private UserRepository $userRepository,
        private PersonRepository $personRepository
    ) {
    }

    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create();
        $users = $this->userRepository->findAll();
        $persons = $this->personRepository->findAll();

        $actors = [];
        $directors = [];
        $producers = [];
        $writers = [];
        $composers = [];

        foreach ($persons as $person) {
            foreach ($person->getSpecialties() as $specialty) {
                switch ($specialty) {
                    case Specialty::ACTOR:
                        $actors[] = $person;
                        break;
                    case Specialty::DIRECTOR:
                        $directors[] = $person;
                        break;
                    case Specialty::PRODUCER:
                        $producers[] = $person;
                        break;
                    case Specialty::WRITER:
                        $writers[] = $person;
                        break;
                    case Specialty::COMPOSER:
                        $composers[] = $person;
                        break;
                }
            }
        }

        for ($i = 0; $i < 20; ++$i) {
            $duration = new \DateTimeImmutable($faker->dateTimeBetween('-1 year', 'now')->format('H:i:s'));

            $ageRestrictions = [0, 6, 12, 18];

            $film = (new Film())
                ->setName($faker->sentence(3))
                ->setAge($faker->randomElement($ageRestrictions))
                ->setReleaseYear($faker->year())
                ->setDuration($duration)
                ->setSlogan($faker->sentence(2))
                ->setDirectedBy(!empty($directors) ? $faker->randomElement($directors) : null)
                ->setProducer(!empty($producers) ? $faker->randomElement($producers) : null)
                ->setWriter(!empty($writers) ? $faker->randomElement($writers) : null)
                ->setComposer(!empty($composers) ? $faker->randomElement($composers) : null)
                ->setDescription($faker->paragraph(3))
                ->setPublisher(!empty($users) ? $faker->randomElement($users) : null);

            if (!empty($actors)) {
                foreach ($faker->randomElements(
                    $actors,
                    min(
                        count($actors),
                        $faker->numberBetween(1, 5)
                    )
                ) as $actor) {
                    $film->addActor($actor);
                }
            }
            foreach (Genres::randomMany() as $genre) {
                $film->addGenre($genre);
            }

            $randAssessmentsCount = $faker->numberBetween(0, 5);
            for ($j = 0; $j < $randAssessmentsCount; ++$j) {
                $author = !empty($users) ? $faker->randomElement($users) : null;
                if ($author) {
                    $assessment = (new Assessment())
                        ->setRating($faker->numberBetween(1, 5))
                        ->setComment($faker->paragraph(3))
                        ->setAuthor($author)
                        ->setFilm($film);
                    $film->addAssessment($assessment);
                }
            }

            $filmAssessments = $film->getAssessments()->toArray();
            if (count($filmAssessments) > 0) {
                $film->setRating(
                    array_sum(array_map(function (Assessment $assessment) {
                        return $assessment->getRating();
                    }, $filmAssessments)) / count($filmAssessments)
                );
            } else {
                $film->setRating(0);
            }

            $manager->persist($film);
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            UserFixtures::class,
            PersonFixtures::class,
        ];
    }
}
