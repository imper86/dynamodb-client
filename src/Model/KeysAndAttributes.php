<?php

declare(strict_types=1);

namespace Imper86\DynamoDBClient\Model;

use InvalidArgumentException;
use Imper86\DynamoDBClient\ValueObject\NonEmptyStringList;
use Imper86\DynamoDBClient\ValueObject\NonEmptyStringMap;
use Webmozart\Assert\Assert;

final class KeysAndAttributes
{
    /**
     * @param null|non-empty-string $projectionExpression
     * @throws InvalidArgumentException
     */
    public function __construct(
        public readonly KeyList $keys,
        public readonly ?NonEmptyStringList $attributesToGet = null,
        public readonly ?bool $consistentRead = null,
        public readonly ?NonEmptyStringMap $expressionAttributeNames = null,
        public readonly ?string $projectionExpression = null,
    ) {
        Assert::minCount($this->keys, 1);
        Assert::maxCount($this->keys, 100);
        Assert::nullOrMinCount($this->attributesToGet, 1);
        Assert::nullOrStringNotEmpty($this->projectionExpression);
    }
}
