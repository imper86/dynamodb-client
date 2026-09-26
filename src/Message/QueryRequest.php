<?php

declare(strict_types=1);

namespace Imper86\DynamoDBClient\Message;

use InvalidArgumentException;
use Imper86\DynamoDBClient\Model\AttributeValueMap;
use Imper86\DynamoDBClient\Model\ConditionalOperator;
use Imper86\DynamoDBClient\Model\ConditionMap;
use Imper86\DynamoDBClient\Model\ReturnConsumedCapacity;
use Imper86\DynamoDBClient\Model\Select;
use Imper86\DynamoDBClient\ValueObject\NonEmptyStringList;
use Imper86\DynamoDBClient\ValueObject\NonEmptyStringMap;
use Webmozart\Assert\Assert;

final class QueryRequest
{
    /**
     * @param non-empty-string $tableName the table name or its ARN
     * @param null|AttributeValueMap $exclusiveStartKey the `LastEvaluatedKey` of the previous page
     * @param null|non-empty-string $filterExpression
     * @param null|non-empty-string $indexName
     * @param null|non-empty-string $keyConditionExpression
     * @param null|positive-int $limit the maximum number of items to evaluate, not to return
     * @param null|non-empty-string $projectionExpression
     * @param null|bool $scanIndexForward false to read the sort key in descending order
     * @throws InvalidArgumentException
     */
    public function __construct(
        public readonly string $tableName,
        public readonly ?NonEmptyStringList $attributesToGet = null,
        public readonly ?ConditionalOperator $conditionalOperator = null,
        public readonly ?bool $consistentRead = null,
        public readonly ?AttributeValueMap $exclusiveStartKey = null,
        public readonly ?NonEmptyStringMap $expressionAttributeNames = null,
        public readonly ?AttributeValueMap $expressionAttributeValues = null,
        public readonly ?string $filterExpression = null,
        public readonly ?string $indexName = null,
        public readonly ?string $keyConditionExpression = null,
        public readonly ?ConditionMap $keyConditions = null,
        public readonly ?int $limit = null,
        public readonly ?string $projectionExpression = null,
        public readonly ?ConditionMap $queryFilter = null,
        public readonly ?ReturnConsumedCapacity $returnConsumedCapacity = null,
        public readonly ?bool $scanIndexForward = null,
        public readonly ?Select $select = null,
    ) {
        Assert::stringNotEmpty($this->tableName);
        Assert::maxLength($this->tableName, 1024);
        Assert::nullOrMinCount($this->attributesToGet, 1);
        Assert::nullOrStringNotEmpty($this->filterExpression);
        Assert::nullOrStringNotEmpty($this->indexName);
        Assert::nullOrMinLength($this->indexName, 3);
        Assert::nullOrMaxLength($this->indexName, 255);
        Assert::nullOrRegex($this->indexName, '/^[a-zA-Z0-9_.\-]+$/');
        Assert::nullOrStringNotEmpty($this->keyConditionExpression);
        Assert::nullOrPositiveInteger($this->limit);
        Assert::nullOrStringNotEmpty($this->projectionExpression);
    }
}
