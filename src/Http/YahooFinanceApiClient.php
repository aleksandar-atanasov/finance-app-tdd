<?php

namespace App\Http;

use App\Contracts\FinanceApiClientInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class YahooFinanceApiClient implements FinanceApiClientInterface
{

    private const URL = 'https://apidojo-yahoo-finance-v1.p.rapidapi.com/stock/v2/get-summary';

    private const X_RAPID_API_HOST = 'apidojo-yahoo-finance-v1.p.rapidapi.com';

    public function __construct(private HttpClientInterface $httpClient, private $rapidApiKey){}

    public function fetchStockProfile(string $symbol, string $region) : JsonResponse
    {
        $response = $this->httpClient->request('GET',self::URL, [
            'query' => [
                'symbol' => $symbol,
                'region' => $region
            ],
            'headers' => [
                'X-RapidAPI-Key' => $this->rapidApiKey,
                'X-RapidAPI-Host' => self::X_RAPID_API_HOST,
            ]
        ]);

        if($response->getStatusCode() !== 200){

            return new JsonResponse('Finance API client error.', 400);
        }

        $stockProfile = json_decode($response->getContent())->price;

        $stockProfileAsArray = [
            'symbol' => $stockProfile->symbol,
            'shortName' => $stockProfile->shortName,
            'region' => $region,
            'exchangeName' => $stockProfile->exchangeName,
            'currency' => $stockProfile->currency,
            'price' => $stockProfile->regularMarketPrice->raw,
            'previousClose' => $stockProfile->regularMarketPreviousClose->raw,
            'priceChange' => round($stockProfile->regularMarketPrice->raw - $stockProfile->regularMarketPreviousClose->raw,2)
        ];

        return new JsonResponse($stockProfileAsArray, 200);
    }
}