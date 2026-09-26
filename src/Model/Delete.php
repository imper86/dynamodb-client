<?php

declare(strict_types=1);

namespace Imper86\DynamoDBClient\Model;

use InvalidArgumentException;
use Imper86\DynamoDBClient\ValueObject\NonEmptyStringMap;
use Webmozart\Assert\Assert;

final class Delete
{
    /**
     * @param AttributeValueMap $key the primary key of the item to delete
     * @param non-empty-string $tableName the table name or its ARN
     * @param null|non-empty-string $conditionExpression
     * @throws InvalidArgumentException
     */
    public function __construct(
        public readonly AttributeValueMap $key,
        public readonly string $tableName,
        public readonly ?string $conditionExpression = null,
        public readonly ?NonEmptyStringMap $expressionAttributeNames = null,
        public readonly ?AttributeValueMap $expressionAttributeValues = null,
        public readonly ?ReturnValuesOnConditionCheckFailure $returnValuesOnConditionCheckFailure = null,
    ) {
        Assert::stringNotEmpty($this->tableName);
        Assert::maxLength($this->tableName, 1024);
        Assert::nullOrStringNotEmpty($this->conditionExpression);
    }
}
