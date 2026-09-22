<?php

declare(strict_types=1);

namespace Imper86\DynamoDBClient\Model;

enum ComparisonOperator: string
{
    case EQ = 'EQ';
    case NE = 'NE';
    case IN = 'IN';
    case LE = 'LE';
    case LT = 'LT';
    case GE = 'GE';
    case GT = 'GT';
    case BETWEEN = 'BETWEEN';
    case NOT_NULL = 'NOT_NULL';
    case NULL = 'NULL';
    case CONTAINS = 'CONTAINS';
    case NOT_CONTAINS = 'NOT_CONTAINS';
    case BEGINS_WITH = 'BEGINS_WITH';
}
