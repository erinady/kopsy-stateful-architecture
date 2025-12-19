<?php

namespace App\Enums\Enums;

enum TransactionMethods: string
{
    case CASH = 'Tunai';
    case CASHLESS = 'Non-Tunai';
}
