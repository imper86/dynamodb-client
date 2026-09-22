<?php

declare(strict_types=1);

namespace Imper86\DynamoDBClient\Model;

enum Select: string
{
    case ALL_ATTRIBUTES = 'ALL_ATTRIBUTES';
    case ALL_PROJECTED_ATTRIBUTES = 'ALL_PROJECTED_ATTRIBUTES';
    case SPECIFIC_ATTRIBUTES = 'SPECIFIC_ATTRIBUTES';
    case COUNT = 'COUNT';
}
