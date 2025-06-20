<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use App\Entity\User;

class UserFixtures extends Fixture
{
    public const ADMIN_USER_REFERENCE = 'admin-user';
    public function load(ObjectManager $manager): void
    {
        $user = new User();
        $user
            ->setUsername("admin")
            ->setEmail("admin@localhost")
            ->setPassword("1234")
            ->setRoles(["ROLE_ADMIN"])
            ->setDisplayName('Admin')
            ->setAge(33)
            ->setAbout('I am an admin')
        ;
        $manager->persist($user);
        $manager->flush();

        $this->addReference(self::ADMIN_USER_REFERENCE, $user);
    }
}
