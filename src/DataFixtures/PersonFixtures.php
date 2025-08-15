<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use App\Entity\Person;
use App\Enum\Gender;
use App\Enum\Specialty;
use Faker\Factory;
use App\Entity\User;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;

class PersonFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create();
        $adminUser = $this->getReference(UserFixtures::ADMIN_USER_REFERENCE, User::class);

        for ($i = 0; $i < 20; $i++) {
            $person = (new Person())
                ->setFirstName($faker->firstName())
                ->setLastName($faker->lastName())
                ->setAge($faker->numberBetween(18, 80))
                ->setInternationalName($faker->name())
                ->setGender(Gender::random())
                ->setBio($faker->text(100))
                ->setPublisher($adminUser);

            foreach (Specialty::randomMany() as $specialty) {
                $person->addSpecialty($specialty);
            }
            $manager->persist($person);
        }
        $manager->flush();

    }
    public function getDependencies(): array
    {
        return [
            UserFixtures::class,
        ];
    }
}
