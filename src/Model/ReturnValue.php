<?php

declare(strict_types=1);

namespace Imper86\DynamoDBClient\Model;

enum ReturnValue: string
{
    case NONE = 'NONE';
    case ALL_OLD = 'ALL_OLD';
    case UPDATED_OLD = 'UPDATED_OLD';
    case ALL_NEW = 'ALL_NEW';
    case UPDATED_NEW = 'UPDATED_NEW';
}
