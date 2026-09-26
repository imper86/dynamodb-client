<?php

declare(strict_types=1);

namespace Imper86\DynamoDBClient\Message;

use InvalidArgumentException;
use Imper86\DynamoDBClient\Model\AttributeValueMap;
use Imper86\DynamoDBClient\Model\ConditionalOperator;
use Imper86\DynamoDBClient\Model\ExpectedAttributeValueMap;
use Imper86\DynamoDBClient\Model\ReturnConsumedCapacity;
use Imper86\DynamoDBClient\Model\ReturnItemCollectionMetrics;
use Imper86\DynamoDBClient\Model\ReturnValue;
use Imper86\DynamoDBClient\Model\ReturnValuesOnConditionCheckFailure;
use Imper86\DynamoDBClient\ValueObject\NonEmptyStringMap;
use Webmozart\Assert\Assert;

final class DeleteItemRequest
{
    /**
     * `ReturnValue` is shared with PutItem and UpdateItem, but DeleteItem accepts only `NONE` and `ALL_OLD`.
     *
     * @param non-empty-string $tableName the table name or its ARN
     * @param null|non-empty-string $conditionExpression
     * @throws InvalidArgumentException
     */
    public function __construct(
        public readonly AttributeValueMap $key,
        public readonly string $tableName,
        public readonly ?ConditionalOperator $conditionalOperator = null,
        public readonly ?string $conditionExpression = null,
        public readonly ?ExpectedAttributeValueMap $expected = null,
        public readonly ?NonEmptyStringMap $expressionAttributeNames = null,
        public readonly ?AttributeValueMap $expressionAttributeValues = null,
        public readonly ?ReturnConsumedCapacity $returnConsumedCapacity = null,
        public readonly ?ReturnItemCollectionMetrics $returnItemCollectionMetrics = null,
        public readonly ?ReturnValue $returnValues = null,
        public readonly ?ReturnValuesOnConditionCheckFailure $returnValuesOnConditionCheckFailure = null,
    ) {
        Assert::stringNotEmpty($this->tableName);
        Assert::maxLength($this->tableName, 1024);
        Assert::nullOrStringNotEmpty($this->conditionExpression);
        Assert::nullOrOneOf($this->returnValues, [ReturnValue::NONE, ReturnValue::ALL_OLD]);
    }
}
