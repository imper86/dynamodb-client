<?php

declare(strict_types=1);

namespace Imper86\DynamoDBClient\Model;

enum TableClass: string
{
    case STANDARD = 'STANDARD';
    case STANDARD_INFREQUENT_ACCESS = 'STANDARD_INFREQUENT_ACCESS';
}
