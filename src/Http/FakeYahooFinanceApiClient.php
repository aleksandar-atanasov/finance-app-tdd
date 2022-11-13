<?php

namespace App\Http;

use App\Contracts\FinanceApiClientInterface;
use Symfony\Component\HttpFoundation\JsonResponse;

class FakeYahooFinanceApiClient implements FinanceApiClientInterface
{
    public static $statusCode = 200;

    public static $content = '';

    public function fetchStockProfile(string $symbol, string $region) : JsonResponse
    {
        /**
         * already a json format , dont encode
         */
        return new JsonResponse(self::$content, self::$statusCode, [], $json = true);
    }

    public static function setContent(array $overrides) : void
    {
        self::$content = json_encode(array_merge(
            [
                'symbol' => 'AMZN',
                'region' => 'US',
                'exchange_name' => 'NasdaqGS',
                'currency' => 'USD',
                'short_name' => 'Amazon.com, Inc.'
            ],
            $overrides
        ));
    }
}