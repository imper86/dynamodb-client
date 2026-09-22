<?php

declare(strict_types=1);

namespace Imper86\DynamoDBClient\Model;

enum ProjectionType: string
{
    case ALL = 'ALL';
    case INCLUDE = 'INCLUDE';
    case KEYS_ONLY = 'KEYS_ONLY';
}
