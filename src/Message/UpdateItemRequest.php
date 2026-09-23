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

final readonly class UpdateItemRequest
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
        public AttributeValueMap $key,
        public string $tableName,
        public ?AttributeValueUpdateMap $attributeUpdates = null,
        public ?ConditionalOperator $conditionalOperator = null,
        public ?string $conditionExpression = null,
        public ?ExpectedAttributeValueMap $expected = null,
        public ?NonEmptyStringMap $expressionAttributeNames = null,
        public ?AttributeValueMap $expressionAttributeValues = null,
        public ?ReturnConsumedCapacity $returnConsumedCapacity = null,
        public ?ReturnItemCollectionMetrics $returnItemCollectionMetrics = null,
        public ?ReturnValue $returnValues = null,
        public ?ReturnValuesOnConditionCheckFailure $returnValuesOnConditionCheckFailure = null,
        public ?string $updateExpression = null,
    ) {
        Assert::stringNotEmpty($this->tableName);
        Assert::maxLength($this->tableName, 1024);
        Assert::nullOrStringNotEmpty($this->conditionExpression);
        Assert::nullOrStringNotEmpty($this->updateExpression);
    }
}
