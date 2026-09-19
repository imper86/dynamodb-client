<?php

declare(strict_types=1);

namespace OoAws\DynamoDBClient\Request;

use InvalidArgumentException;
use OoAws\DynamoDBClient\Model\AttributeValueObjectMap;
use OoAws\DynamoDBClient\Model\ReturnConsumedCapacity;
use OoAws\DynamoDBClient\ValueObject\NonEmptyStringList;
use OoAws\DynamoDBClient\ValueObject\NonEmptyStringMap;
use Webmozart\Assert\Assert;

final readonly class GetItemRequest
{
    /**
     * @throws InvalidArgumentException
     */
    public function __construct(
        public AttributeValueObjectMap $key,
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
