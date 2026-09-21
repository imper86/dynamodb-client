<?php

declare(strict_types=1);

namespace Imper86\DynamoDBClient\Model;

use InvalidArgumentException;
use Imper86\DynamoDBClient\ValueObject\NonEmptyStringList;
use Imper86\DynamoDBClient\ValueObject\NonEmptyStringMap;
use Webmozart\Assert\Assert;

final readonly class KeysAndAttributes
{
    /**
     * @param null|non-empty-string $projectionExpression
     * @throws InvalidArgumentException
     */
    public function __construct(
        public KeyList $keys,
        public ?NonEmptyStringList $attributesToGet = null,
        public ?bool $consistentRead = null,
        public ?NonEmptyStringMap $expressionAttributeNames = null,
        public ?string $projectionExpression = null,
    ) {
        Assert::minCount($this->keys, 1);
        Assert::maxCount($this->keys, 100);
        Assert::nullOrMinCount($this->attributesToGet, 1);
        Assert::nullOrStringNotEmpty($this->projectionExpression);
    }
}
