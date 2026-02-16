<?php

namespace App\Enums;

enum TransactionTypeEnum: string
{
    case PAYIN = 'payin';
    case PAYOUT = 'payout';
    case EXCHANGE_IN = 'exchange_in';
    case EXCHANGE_OUT = 'exchange_out';
}
