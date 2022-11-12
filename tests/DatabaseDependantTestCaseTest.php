<?php

namespace App\Tests;

use App\Tests\DatabasePrimer;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

abstract class DatabaseDependantTestCaseTest extends KernelTestCase
{
    protected ?EntityManagerInterface $entityManager;

    public function setUp() : void
    {
        $kernel = self::bootKernel();

        DatabasePrimer::prime($kernel);

        $this->entityManager = $kernel->getContainer()->get('doctrine')->getManager();
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        $this->entityManager->close();

        $this->entityManager = null;
    }
}