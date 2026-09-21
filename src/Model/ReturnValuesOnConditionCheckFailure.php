<?php

declare(strict_types=1);

namespace Imper86\DynamoDBClient\Model;

enum ReturnValuesOnConditionCheckFailure: string
{
    case ALL_OLD = 'ALL_OLD';
    case NONE = 'NONE';
}
