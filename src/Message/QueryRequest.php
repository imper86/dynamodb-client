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

final readonly class QueryRequest
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
        public string $tableName,
        public ?NonEmptyStringList $attributesToGet = null,
        public ?ConditionalOperator $conditionalOperator = null,
        public ?bool $consistentRead = null,
        public ?AttributeValueMap $exclusiveStartKey = null,
        public ?NonEmptyStringMap $expressionAttributeNames = null,
        public ?AttributeValueMap $expressionAttributeValues = null,
        public ?string $filterExpression = null,
        public ?string $indexName = null,
        public ?string $keyConditionExpression = null,
        public ?ConditionMap $keyConditions = null,
        public ?int $limit = null,
        public ?string $projectionExpression = null,
        public ?ConditionMap $queryFilter = null,
        public ?ReturnConsumedCapacity $returnConsumedCapacity = null,
        public ?bool $scanIndexForward = null,
        public ?Select $select = null,
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
