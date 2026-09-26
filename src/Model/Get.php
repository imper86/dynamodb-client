<?php

declare(strict_types=1);

namespace Imper86\DynamoDBClient\Model;

use InvalidArgumentException;
use Imper86\DynamoDBClient\ValueObject\NonEmptyStringMap;
use Webmozart\Assert\Assert;

final class Get
{
    /**
     * @param non-empty-string $tableName the table name or its ARN
     * @param null|non-empty-string $projectionExpression
     * @throws InvalidArgumentException
     */
    public function __construct(
        public readonly AttributeValueMap $key,
        public readonly string $tableName,
        public readonly ?NonEmptyStringMap $expressionAttributeNames = null,
        public readonly ?string $projectionExpression = null,
    ) {
        Assert::stringNotEmpty($this->tableName);
        Assert::maxLength($this->tableName, 1024);
        Assert::nullOrStringNotEmpty($this->projectionExpression);
    }
}
