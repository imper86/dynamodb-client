<?php

declare(strict_types=1);

namespace Imper86\DynamoDBClient\Model;

use InvalidArgumentException;
use Imper86\DynamoDBClient\ValueObject\NonEmptyStringMap;
use Webmozart\Assert\Assert;

final readonly class ConditionCheck
{
    /**
     * @param non-empty-string $conditionExpression the condition the item must meet for the transaction to succeed
     * @param AttributeValueMap $key the primary key of the item to check
     * @param non-empty-string $tableName the table name or its ARN
     * @throws InvalidArgumentException
     */
    public function __construct(
        public string $conditionExpression,
        public AttributeValueMap $key,
        public string $tableName,
        public ?NonEmptyStringMap $expressionAttributeNames = null,
        public ?AttributeValueMap $expressionAttributeValues = null,
        public ?ReturnValuesOnConditionCheckFailure $returnValuesOnConditionCheckFailure = null,
    ) {
        Assert::stringNotEmpty($this->conditionExpression);
        Assert::stringNotEmpty($this->tableName);
        Assert::maxLength($this->tableName, 1024);
    }
}
