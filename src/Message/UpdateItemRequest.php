<?php

declare(strict_types=1);

namespace Imper86\DynamoDBClient\Message;

use InvalidArgumentException;
use Imper86\DynamoDBClient\Model\AttributeValueMap;
use Imper86\DynamoDBClient\Model\AttributeValueUpdateMap;
use Imper86\DynamoDBClient\Model\ConditionalOperator;
use Imper86\DynamoDBClient\Model\ExpectedAttributeValueMap;
use Imper86\DynamoDBClient\Model\ReturnConsumedCapacity;
use Imper86\DynamoDBClient\Model\ReturnItemCollectionMetrics;
use Imper86\DynamoDBClient\Model\ReturnValue;
use Imper86\DynamoDBClient\Model\ReturnValuesOnConditionCheckFailure;
use Imper86\DynamoDBClient\ValueObject\NonEmptyStringMap;
use Webmozart\Assert\Assert;

final class UpdateItemRequest
{
    /**
     * @param AttributeValueMap $key the primary key of the item to update
     * @param non-empty-string $tableName the table name or its ARN
     * @param null|AttributeValueUpdateMap $attributeUpdates the legacy alternative to `$updateExpression`
     * @param null|non-empty-string $conditionExpression
     * @param null|non-empty-string $updateExpression the attributes to change, and how
     * @throws InvalidArgumentException
     */
    public function __construct(
        public readonly AttributeValueMap $key,
        public readonly string $tableName,
        public readonly ?AttributeValueUpdateMap $attributeUpdates = null,
        public readonly ?ConditionalOperator $conditionalOperator = null,
        public readonly ?string $conditionExpression = null,
        public readonly ?ExpectedAttributeValueMap $expected = null,
        public readonly ?NonEmptyStringMap $expressionAttributeNames = null,
        public readonly ?AttributeValueMap $expressionAttributeValues = null,
        public readonly ?ReturnConsumedCapacity $returnConsumedCapacity = null,
        public readonly ?ReturnItemCollectionMetrics $returnItemCollectionMetrics = null,
        public readonly ?ReturnValue $returnValues = null,
        public readonly ?ReturnValuesOnConditionCheckFailure $returnValuesOnConditionCheckFailure = null,
        public readonly ?string $updateExpression = null,
    ) {
        Assert::stringNotEmpty($this->tableName);
        Assert::maxLength($this->tableName, 1024);
        Assert::nullOrStringNotEmpty($this->conditionExpression);
        Assert::nullOrStringNotEmpty($this->updateExpression);
    }
}
