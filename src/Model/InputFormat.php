<?php

declare(strict_types=1);

namespace Imper86\DynamoDBClient\Model;

enum InputFormat: string
{
    case CSV = 'CSV';
    case DYNAMODB_JSON = 'DYNAMODB_JSON';
    case ION = 'ION';
}
