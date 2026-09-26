<?php

declare(strict_types=1);

namespace Imper86\DynamoDBClient\Model;

use InvalidArgumentException;
use Imper86\DynamoDBClient\ValueObject\NonEmptyStringMap;
use Webmozart\Assert\Assert;

final class ConditionCheck
{
    /**
     * @param non-empty-string $conditionExpression the condition the item must meet for the transaction to succeed
     * @param AttributeValueMap $key the primary key of the item to check
     * @param non-empty-string $tableName the table name or its ARN
     * @throws InvalidArgumentException
     */
    public function __construct(
        public readonly string $conditionExpression,
        public readonly AttributeValueMap $key,
        public readonly string $tableName,
        public readonly ?NonEmptyStringMap $expressionAttributeNames = null,
        public readonly ?AttributeValueMap $expressionAttributeValues = null,
        public readonly ?ReturnValuesOnConditionCheckFailure $returnValuesOnConditionCheckFailure = null,
    ) {
        Assert::stringNotEmpty($this->conditionExpression);
        Assert::stringNotEmpty($this->tableName);
        Assert::maxLength($this->tableName, 1024);
    }
}
