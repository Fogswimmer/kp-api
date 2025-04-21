<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use App\Entity\User;

class UserFixtures extends Fixture{
    public function load(ObjectManager $manager): void{
        $user = new User();
        $user
        ->setUsername("admin")
        ->setEmail("admin@localhost")
        ->setPassword("1234")
        ->setRoles(["ROLE_ADMIN"] )
        ->setDisplayName('Admin')
        ->setAge(33)
        ->setAbout('I am an admin')
        ;
        $manager->persist($user);
        $manager->flush();
    }
}

