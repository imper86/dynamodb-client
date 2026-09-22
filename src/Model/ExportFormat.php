<?php

declare(strict_types=1);

namespace Imper86\DynamoDBClient\Model;

enum ExportFormat: string
{
    case DYNAMODB_JSON = 'DYNAMODB_JSON';
    case ION = 'ION';
}
