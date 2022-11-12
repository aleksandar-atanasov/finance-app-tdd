<?php

namespace App\Http;

use App\Contracts\FinanceApiClientInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class YahooFinanceApiClient implements FinanceApiClientInterface
{

    private const URL = 'https://apidojo-yahoo-finance-v1.p.rapidapi.com/auto-complete';

    private const X_RAPID_API_HOST = 'apidojo-yahoo-finance-v1.p.rapidapi.com';

    public function __construct(private HttpClientInterface $httpClient, private $rapidApiKey)
    {

    }

    public function fetchStockProfile(string $symbol, string $region) : array
    {
        $response = $this->httpClient->request('GET',self::URL, [
            'query' => [
                'q' => 'amazon',
                'region' => 'US'
            ],
            'headers' => [
                'X-RapidAPI-Key' => $this->rapidApiKey,
                'X-RapidAPI-Host' => self::X_RAPID_API_HOST,
            ]
        ]);

        dd($response->getContent());
    }

}