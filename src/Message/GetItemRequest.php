<?php

declare(strict_types=1);

namespace OoAws\DynamoDBClient\Message;

use InvalidArgumentException;
use OoAws\DynamoDBClient\Model\AttributeValueMap;
use OoAws\DynamoDBClient\Model\ReturnConsumedCapacity;
use OoAws\DynamoDBClient\ValueObject\NonEmptyStringList;
use OoAws\DynamoDBClient\ValueObject\NonEmptyStringMap;
use Webmozart\Assert\Assert;

final readonly class GetItemRequest
{
    /**
     * @param non-empty-string $tableName
     * @param null|non-empty-string $projectionExpression
     * @throws InvalidArgumentException
     */
    public function __construct(
        public AttributeValueMap $key,
        public string $tableName,
        public ?NonEmptyStringList $attributesToGet = null,
        public ?bool $consistentRead = null,
        public ?NonEmptyStringMap $expressionAttributeNames = null,
        public ?string $projectionExpression = null,
        public ?ReturnConsumedCapacity $returnConsumedCapacity = null,
    ) {
        Assert::stringNotEmpty($this->tableName);
        Assert::nullOrStringNotEmpty($this->projectionExpression);
    }
}
