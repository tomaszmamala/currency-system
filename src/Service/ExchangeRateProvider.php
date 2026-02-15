<?php

namespace App\Service;

use App\Enums\CurrencyEnum;

class ExchangeRateProvider
{
    public function getRate(CurrencyEnum $from, CurrencyEnum $to): string
    {
        return '1.1';
    }
}
