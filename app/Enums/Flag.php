<?php

namespace App\Enums;

enum Flag: string
{
    case INTERNAL_TRANSACTION = 'internal-transaction';
    case INVESTMENT = 'investment';
    case EXCHANGE = 'exchange';
}
