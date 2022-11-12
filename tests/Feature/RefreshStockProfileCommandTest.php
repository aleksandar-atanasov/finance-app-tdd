<?php

namespace App\Tests\Feature;

use App\Entity\Stock;
use App\Tests\DatabaseDependantTestCaseTest;
use App\Tests\DatabasePrimer;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Tester\CommandTester;

class RefreshStockProfileCommandTest extends DatabaseDependantTestCaseTest
{
    /**
     * @test
     */
    public function the_refresh_stock_profile_command_behaves_correcty_when_a_stock_record_does_not_exist()
    {

        // Set Up
        $app = new Application(self::$kernel);

        $command = $app->find('app:refresh-stock-profile');

        $commandTester = new CommandTester($command);

        // Do Something
        $commandTester->execute([
            'symbol' => 'AMZN',
            'region' => 'US'
        ]);

        // Assert
        $repository = $this->entityManager->getRepository(Stock::class);

        /**
         * @var Stock $stock
         */
        $stock = $repository->findOneBy(['symbol' => 'AMZN']);

        $this->assertSame('USD', $stock->getCurrency());
        $this->assertSame('NasdaqGS', $stock->getExchangeName());
        $this->assertSame('AMZN', $stock->getSymbol());
        $this->assertSame('Amazon.com, Inc.', $stock->getShortName());
        $this->assertSame('US', $stock->getRegion());
        $this->assertGreaterThan(50,$stock->getPreviousClose());
        $this->assertGreaterThan(50,$stock->getPrice());


    }
}