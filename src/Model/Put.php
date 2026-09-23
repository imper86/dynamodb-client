<?php

declare(strict_types=1);

namespace Imper86\DynamoDBClient\Model;

use InvalidArgumentException;
use Imper86\DynamoDBClient\ValueObject\NonEmptyStringMap;
use Webmozart\Assert\Assert;

final readonly class Put
{
    /**
     * @param AttributeValueMap $item the whole item, primary key attributes included
     * @param non-empty-string $tableName the table name or its ARN
     * @param null|non-empty-string $conditionExpression
     * @throws InvalidArgumentException
     */
    public function __construct(
        public AttributeValueMap $item,
        public string $tableName,
        public ?string $conditionExpression = null,
        public ?NonEmptyStringMap $expressionAttributeNames = null,
        public ?AttributeValueMap $expressionAttributeValues = null,
        public ?ReturnValuesOnConditionCheckFailure $returnValuesOnConditionCheckFailure = null,
    ) {
        Assert::stringNotEmpty($this->tableName);
        Assert::maxLength($this->tableName, 1024);
        Assert::nullOrStringNotEmpty($this->conditionExpression);
    }
}
