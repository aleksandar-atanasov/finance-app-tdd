<?php

namespace App\Contracts;

use Symfony\Component\HttpFoundation\JsonResponse;

interface FinanceApiClientInterface
{
    public function fetchStockProfile(string $symbol, string $region) : JsonResponse;
}