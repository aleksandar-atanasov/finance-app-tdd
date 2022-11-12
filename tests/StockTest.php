<?php

namespace App\Tests;

use App\Entity\Stock;
use App\Tests\DatabasePrimer;
use Doctrine\ORM\EntityManagerInterface;
use App\Tests\DatabaseDependantTestCaseTest;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class StockTest extends DatabaseDependantTestCaseTest
{
    /**
     * @test
     */
    public function a_stock_record_can_be_created_in_the_database(): void
    {
        //Set Up

        //Stock
        $stock = new Stock();
        $stock->setSymbol('AMZN');
        $stock->setShortName('Amazon Inc');
        $stock->setCurrency('USD');
        $stock->setExchangeName('Nasdaq');
        $stock->setRegion('US');

        //Price
        $price = 1000;
        $previousClose = 1100;
        $priceChange = $price - $previousClose;

        $stock->setPrice($price);
        $stock->setPreviousClose($previousClose);
        $stock->setPriceChange($priceChange);

        //Do Something
        $this->entityManager->persist($stock);

        $this->entityManager->flush();

        $stockRepository = $this->entityManager->getRepository(Stock::class);

        $stockRecord = $stockRepository->findOneBy(['symbol' => 'AMZN']);

        //Assert
        $this->assertEquals('Amazon Inc',$stockRecord->getShortName());
        $this->assertEquals('USD',$stockRecord->getCurrency());
        $this->assertEquals('Nasdaq',$stockRecord->getExchangeName());
        $this->assertEquals('US',$stockRecord->getRegion());
        $this->assertIsNumeric($stockRecord->getPrice());
        $this->assertEquals('1000',$stockRecord->getPrice());
        $this->assertEquals('1000',$stockRecord->getPrice());
        $this->assertEquals('1100',$stockRecord->getPreviousClose());
        $this->assertEquals('-100',$stockRecord->getPriceChange());
    }
}