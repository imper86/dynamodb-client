<?php

declare(strict_types=1);

namespace Imper86\DynamoDBClient\Model;

use InvalidArgumentException;

use function array_values;
use function count;

/**
 * A legacy condition on one attribute: a `ComparisonOperator` and the values it compares against.
 */
final class Condition
{
    /**
     * @throws InvalidArgumentException
     */
    public function __construct(
        public readonly ComparisonOperator $comparisonOperator,
        public readonly ?AttributeValueList $attributeValueList = null,
    ) {
        $this->comparisonOperator->assertOperandCount(count($this->attributeValueList ?? []));
    }

    /**
     * @throws InvalidArgumentException
     */
    public static function comparison(ComparisonOperator $comparisonOperator, AttributeValue ...$values): self
    {
        return new self(
            comparisonOperator: $comparisonOperator,
            attributeValueList: [] === $values ? null : new AttributeValueList(array_values($values)),
        );
    }
}
