<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use App\Entity\Person;
use App\Enum\Gender;
use App\Enum\Specialty;
use App\Repository\UserRepository;

class PersonFixtures extends Fixture
{
    public function __construct(
        private UserRepository $userRepository
    ){}
    public function load(ObjectManager $manager): void
    {
        $admin = $this->userRepository->findOneBy(["username"=> "admin"]);
        $firstnames = ['John', "Mary", "Mark", "Ben", "Sally"];
        $lastnames = ['Doe', "Smith", "Green", "Brown", "White"];
        $birthdays = ['1990-01-01', '1991-01-01', '1992-01-01', '1993-01-01', '1994-01-01'];
        $genders = [Gender::MALE, Gender::FEMALE, Gender::MALE, Gender::MALE, Gender::FEMALE];
        $specialtyIds = [1, 2, 3, 4, 5];
        $bios = [
            'John Doe is a talented software engineer with a passion for creating innovative solutions.',
            'Mary Smith is a talented software engineer with a passion for creating innovative solutions.',
            'Mark Green is a talented software engineer with a passion for creating innovative solutions.',
            'Ben Brown is a talented software engineer with a passion for creating innovative solutions.',
            'Sally White is a talented software engineer with a passion for creating innovative solutions.',
        ];
        foreach ($specialtyIds as $specialtyId) {
            $specialties[] = Specialty::matchIdAndSpecialty($specialtyId);
        }

        for ($i = 0; $i < 5; $i++) {
            $person = new Person();
            $person->setFirstname($firstnames[$i]);
            $person->setLastname($lastnames[$i]);
            $person->setBirthday(new \DateTimeImmutable
            ($birthdays[$i]));
            $person->setGender($genders[$i]);
            $person->setSpecialties($specialties);
            $person->setBio($bios[$i]);
            $person->setPublisher( $admin);
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
