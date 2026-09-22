<?php

declare(strict_types=1);

namespace Imper86\DynamoDBClient\Model;

enum ScalarAttributeType: string
{
    case BINARY = 'B';
    case NUMBER = 'N';
    case STRING = 'S';
}
