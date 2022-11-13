<?php

namespace App\Tests\Feature;

use App\Entity\Stock;
use App\Http\FakeYahooFinanceApiClient;
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
    public function the_refresh_stock_profile_command_creates_new_records_correctly()
    {
        // Set Up
        $app = new Application(self::$kernel);

        $command = $app->find('app:refresh-stock-profile');

        $commandTester = new CommandTester($command);

        FakeYahooFinanceApiClient::$content = '{"symbol":"AMZN","shortName":"Amazon.com, Inc.","region":"US","exchangeName":"NasdaqGS","currency":"USD","price":100.79,"previousClose":96.63,"priceChange":4.16}';

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

    /**
     * @test
     */
    public function the_refresh_stock_profile_command_updates_existing_records_correctly()
    {
        // Set Up
        $stock = new Stock();

        $stock->setSymbol('AMZN');
        $stock->setShortName('Amazon.com, Inc.');
        $stock->setRegion('US');
        $stock->setExchangeName('NasdaqGS');
        $stock->setCurrency('USD');
        $stock->setPreviousClose(3000);
        $stock->setPrice(3100);
        $stock->setPriceChange(100);

        $this->entityManager->persist($stock);
        $this->entityManager->flush();

        $stockId = $stock->getId();

        $app = new Application(self::$kernel);

        $command = $app->find('app:refresh-stock-profile');

        $commandTester = new CommandTester($command);

        FakeYahooFinanceApiClient::$statusCode = 200;

        FakeYahooFinanceApiClient::setContent([
            'previous_close' => 96.63,
            'price' => 100.79,
            'price_change' => 4.16
        ]);

        // Do Something
        $commandStatus = $commandTester->execute([
            'symbol' => 'AMZN',
            'region' => 'US'
        ]);

        // Assert
        $repository = $this->entityManager->getRepository(Stock::class);

        /**
         * @var Stock $stockRecord
         */
        $stockRecord = $repository->find($stockId);

        $stockRecordCount = $repository->createQueryBuilder('stock')
                                        ->select("COUNT(stock.id)")
                                        ->getQuery()
                                        ->getSingleScalarResult();


        $this->assertEquals(100.79, $stockRecord->getPrice());

        $this->assertEquals(96.63, $stockRecord->getPreviousClose());

        $this->assertEquals(4.16, $stockRecord->getPriceChange());

        $this->assertEquals(0, $commandStatus);

        // check that there are no duplicates in the database only 1 record is found
        $this->assertEquals(1, $stockRecordCount);

    }

    /**
     * @test
     */
    public function non_200_responses_are_handled_correcty()
    {
        // Set Up
        $app = new Application(self::$kernel);

        $command = $app->find('app:refresh-stock-profile');

        $commandTester = new CommandTester($command);

        // set a non 200 status code
        FakeYahooFinanceApiClient::$statusCode = 500;

        FakeYahooFinanceApiClient::$content = 'Finance API client error.';

        // Do Something
        $commandStatus = $commandTester->execute([
            'symbol' => 'AMZN',
            'region' => 'US'
        ]);

        $repository = $this->entityManager->getRepository(Stock::class);

        $stockRecordCount = $repository->createQueryBuilder('stock')
                                       ->select("COUNT(stock.id)")
                                       ->getQuery()
                                       ->getSingleScalarResult();

        // Assert
        $this->assertEquals(1, $commandStatus);

        $this->assertEquals(0, $stockRecordCount);
    }
}