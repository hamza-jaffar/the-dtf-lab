<?php

namespace App\Enums;

enum Payment: string
{
    case EasyPaisa = 'easypaisa';
    case JazzCash = 'jazzcash';
    case NayaPay = 'nayapay';
    case SadaPay = 'sadapay';

    case UPaisa = 'upaisa';

    case Raast = 'raast';
    case IBFT = 'ibft';

    case Visa = 'visa';
    case Mastercard = 'mastercard';

    case BankTransfer = 'bank_transfer';

    case Cash = 'cash';
}
