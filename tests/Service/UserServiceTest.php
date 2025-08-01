<?php

namespace App\Tests\Service;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Foundry\Test\ResetDatabase;

class UserServiceTest extends KernelTestCase
{
    use ResetDatabase;
    use Factories;

    public function testLoginUpdatesLastLoginAndPersistsUser(): void
    {
        $this->markTestIncomplete();
    }
}
