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

final readonly class ScanRequest
{
    /**
     * A parallel scan gives each worker the same `TotalSegments` and its own zero-based `Segment`; the two
     * go together, and a segment must be below the total.
     *
     * @param non-empty-string $tableName the table name or its ARN
     * @param null|AttributeValueMap $exclusiveStartKey the `LastEvaluatedKey` of the previous page, from the
     *                                                  same segment
     * @param null|non-empty-string $filterExpression
     * @param null|non-empty-string $indexName
     * @param null|positive-int $limit the maximum number of items to evaluate, not to return
     * @param null|non-empty-string $projectionExpression
     * @param null|int<0, 999999> $segment
     * @param null|int<1, 1000000> $totalSegments
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
        public ?int $limit = null,
        public ?string $projectionExpression = null,
        public ?ReturnConsumedCapacity $returnConsumedCapacity = null,
        public ?ConditionMap $scanFilter = null,
        public ?int $segment = null,
        public ?Select $select = null,
        public ?int $totalSegments = null,
    ) {
        Assert::stringNotEmpty($this->tableName);
        Assert::maxLength($this->tableName, 1024);
        Assert::nullOrMinCount($this->attributesToGet, 1);
        Assert::nullOrStringNotEmpty($this->filterExpression);
        Assert::nullOrStringNotEmpty($this->indexName);
        Assert::nullOrMinLength($this->indexName, 3);
        Assert::nullOrMaxLength($this->indexName, 255);
        Assert::nullOrRegex($this->indexName, '/^[a-zA-Z0-9_.\-]+$/');
        Assert::nullOrPositiveInteger($this->limit);
        Assert::nullOrStringNotEmpty($this->projectionExpression);
        Assert::nullOrRange($this->segment, 0, 999999);
        Assert::nullOrRange($this->totalSegments, 1, 1000000);
        Assert::same(
            null === $this->segment,
            null === $this->totalSegments,
            'Segment and TotalSegments must be given together.',
        );

        if (null !== $this->segment && null !== $this->totalSegments) {
            Assert::lessThan($this->segment, $this->totalSegments, 'Segment must be less than TotalSegments.');
        }
    }
}
