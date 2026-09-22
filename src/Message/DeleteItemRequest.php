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

final readonly class DeleteItemRequest
{
    /**
     * `ReturnValue` is shared with PutItem and UpdateItem, but DeleteItem accepts only `NONE` and `ALL_OLD`.
     *
     * @param non-empty-string $tableName the table name or its ARN
     * @param null|non-empty-string $conditionExpression
     * @throws InvalidArgumentException
     */
    public function __construct(
        public AttributeValueMap $key,
        public string $tableName,
        public ?ConditionalOperator $conditionalOperator = null,
        public ?string $conditionExpression = null,
        public ?ExpectedAttributeValueMap $expected = null,
        public ?NonEmptyStringMap $expressionAttributeNames = null,
        public ?AttributeValueMap $expressionAttributeValues = null,
        public ?ReturnConsumedCapacity $returnConsumedCapacity = null,
        public ?ReturnItemCollectionMetrics $returnItemCollectionMetrics = null,
        public ?ReturnValue $returnValues = null,
        public ?ReturnValuesOnConditionCheckFailure $returnValuesOnConditionCheckFailure = null,
    ) {
        Assert::stringNotEmpty($this->tableName);
        Assert::maxLength($this->tableName, 1024);
        Assert::nullOrStringNotEmpty($this->conditionExpression);
        Assert::nullOrOneOf($this->returnValues, [ReturnValue::NONE, ReturnValue::ALL_OLD]);
    }
}
