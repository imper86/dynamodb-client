<?php

declare(strict_types=1);

namespace Imper86\DynamoDBClient\Model;

enum ConditionalOperator: string
{
    case AND = 'AND';
    case OR = 'OR';
}
