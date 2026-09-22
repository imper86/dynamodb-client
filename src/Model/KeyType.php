<?php

declare(strict_types=1);

namespace Imper86\DynamoDBClient\Model;

enum KeyType: string
{
    case HASH = 'HASH';
    case RANGE = 'RANGE';
}
