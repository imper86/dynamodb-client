<?php

declare(strict_types=1);

namespace Imper86\DynamoDBClient\Model;

use InvalidArgumentException;
use Webmozart\Assert\Assert;

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

    /**
     * Checks that an `AttributeValueList` holds as many values as this operator compares against.
     *
     * @throws InvalidArgumentException
     */
    public function assertOperandCount(int $operands): void
    {
        match ($this) {
            self::NULL, self::NOT_NULL => Assert::same(
                $operands,
                0,
                'Expected no AttributeValueList for ' . $this->value . '. Got %s values',
            ),
            self::BETWEEN => Assert::same(
                $operands,
                2,
                'Expected 2 values in the AttributeValueList for BETWEEN. Got: %s',
            ),
            self::IN => Assert::greaterThanEq(
                $operands,
                1,
                'Expected at least 1 value in the AttributeValueList for IN. Got: %s',
            ),
            default => Assert::same(
                $operands,
                1,
                'Expected 1 value in the AttributeValueList for ' . $this->value . '. Got: %s',
            ),
        };
    }
}
