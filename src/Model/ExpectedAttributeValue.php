<?php

declare(strict_types=1);

namespace Imper86\DynamoDBClient\Model;

use InvalidArgumentException;
use Webmozart\Assert\Assert;

use function array_values;
use function count;

/**
 * A legacy condition on one attribute, in one of two exclusive forms: a `Value` (optionally with `Exists`),
 * or a `ComparisonOperator` with the `AttributeValueList` it compares against.
 */
final readonly class ExpectedAttributeValue
{
    /**
     * @throws InvalidArgumentException
     */
    public function __construct(
        public ?AttributeValueList $attributeValueList = null,
        public ?ComparisonOperator $comparisonOperator = null,
        public ?bool $exists = null,
        public ?AttributeValue $value = null,
    ) {
        if ($this->comparisonOperator instanceof ComparisonOperator || $this->attributeValueList instanceof AttributeValueList) {
            Assert::notNull($this->comparisonOperator, 'An AttributeValueList needs a ComparisonOperator.');
            Assert::true(
                null === $this->exists && !$this->value instanceof AttributeValue,
                'Value and Exists cannot be combined with ComparisonOperator and AttributeValueList.',
            );

            $this->comparisonOperator->assertOperandCount(count($this->attributeValueList ?? []));

            return;
        }

        if (false === $this->exists) {
            Assert::null($this->value, 'A Value cannot be expected when Exists is false.');

            return;
        }

        Assert::notNull($this->value, 'Expected a Value, a ComparisonOperator, or Exists set to false.');
    }

    /**
     * @throws InvalidArgumentException
     */
    public static function comparison(ComparisonOperator $comparisonOperator, AttributeValue ...$values): self
    {
        return new self(
            attributeValueList: [] === $values ? null : new AttributeValueList(array_values($values)),
            comparisonOperator: $comparisonOperator,
        );
    }

    /**
     * Expects the attribute to be absent from the item.
     *
     * @throws InvalidArgumentException
     */
    public static function notExists(): self
    {
        return new self(exists: false);
    }

    /**
     * Expects the attribute to exist and hold exactly this value.
     *
     * @throws InvalidArgumentException
     */
    public static function value(AttributeValue $value): self
    {
        return new self(value: $value);
    }
}
