<?php

namespace App\Tests\Integration;

use App\Tests\DatabaseDependantTestCaseTest;

class YahooFinanceApiClientTest extends DatabaseDependantTestCaseTest
{

    /**
     * @test
     * @group integration
     */
    public function the_yahoo_finance_api_client_returns_correct_data()
    {
        //Set Up
        $yahooFinanceApiClient = self::$kernel->getContainer()->get('yahoo-finance-api-client');

        // Do Something
        $response = $yahooFinanceApiClient->fetchStockProfile('AMZN','US');

        $stockProfile = json_decode($response['content']);

        // Assert
        $this->assertSame('AMZN', $stockProfile->symbol);
        $this->assertSame('Amazon.com, Inc.', $stockProfile->shortName);
        $this->assertSame('US', $stockProfile->region);
        $this->assertSame('NasdaqGS', $stockProfile->exchangeName);
        $this->assertSame('USD', $stockProfile->currency);
        $this->assertIsNumeric($stockProfile->price);
        $this->assertIsNumeric($stockProfile->previousClose);
        $this->assertIsNumeric($stockProfile->priceChange);
    }

}
