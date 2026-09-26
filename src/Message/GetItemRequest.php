<?php

declare(strict_types=1);

namespace Imper86\DynamoDBClient\Message;

use InvalidArgumentException;
use Imper86\DynamoDBClient\Model\AttributeValueMap;
use Imper86\DynamoDBClient\Model\ReturnConsumedCapacity;
use Imper86\DynamoDBClient\ValueObject\NonEmptyStringList;
use Imper86\DynamoDBClient\ValueObject\NonEmptyStringMap;
use Webmozart\Assert\Assert;

final class GetItemRequest
{
    /**
     * @param non-empty-string $tableName the table name or its ARN
     * @param null|non-empty-string $projectionExpression
     * @throws InvalidArgumentException
     */
    public function __construct(
        public readonly AttributeValueMap $key,
        public readonly string $tableName,
        public readonly ?NonEmptyStringList $attributesToGet = null,
        public readonly ?bool $consistentRead = null,
        public readonly ?NonEmptyStringMap $expressionAttributeNames = null,
        public readonly ?string $projectionExpression = null,
        public readonly ?ReturnConsumedCapacity $returnConsumedCapacity = null,
    ) {
        Assert::stringNotEmpty($this->tableName);
        Assert::maxLength($this->tableName, 1024);
        Assert::nullOrMinCount($this->attributesToGet, 1);
        Assert::nullOrStringNotEmpty($this->projectionExpression);
    }
}
