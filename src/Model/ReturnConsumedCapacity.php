<?php

declare(strict_types=1);

namespace OoAws\DynamoDBClient\Model;

enum ReturnConsumedCapacity: string
{
    case INDEXES = 'INDEXES';
    case TOTAL = 'TOTAL';
    case NONE = 'NONE';
}
