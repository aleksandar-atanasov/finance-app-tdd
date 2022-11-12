<?php

namespace App\Contracts;

interface FinanceApiClientInterface
{
    public function fetchStockProfile(string $symbol, string $region);
}