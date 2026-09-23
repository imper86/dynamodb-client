<?php

declare(strict_types=1);

namespace Imper86\DynamoDBClient\Message;

use InvalidArgumentException;
use Imper86\DynamoDBClient\Model\AttributeValue;
use Imper86\DynamoDBClient\Model\AttributeValueList;
use Imper86\DynamoDBClient\Model\AttributeValueMap;
use Imper86\DynamoDBClient\Model\ReturnConsumedCapacity;
use Imper86\DynamoDBClient\ValueObject\NonEmptyStringMap;
use Webmozart\Assert\Assert;

use function array_map;
use function array_values;

final readonly class SearchVectorsRequest
{
    /**
     * The reference caps `TopK` at 100 in prose while the service model sets no maximum, so the cap is
     * left to the service.
     *
     * @param non-empty-string $indexName the vector index to search
     * @param AttributeValueList $searchVector one number per dimension of the index, as 32-bit floats
     * @param non-empty-string $tableName the table name or its ARN
     * @param positive-int $topK the number of most similar items to return
     * @param null|non-empty-string $projectionExpression
     * @param null|non-empty-string $searchConditionExpression
     * @throws InvalidArgumentException
     */
    public function __construct(
        public string $indexName,
        public AttributeValueList $searchVector,
        public string $tableName,
        public int $topK,
        public ?NonEmptyStringMap $expressionAttributeNames = null,
        public ?AttributeValueMap $expressionAttributeValues = null,
        public ?string $projectionExpression = null,
        public ?ReturnConsumedCapacity $returnConsumedCapacity = null,
        public ?string $searchConditionExpression = null,
    ) {
        Assert::stringNotEmpty($this->indexName);
        Assert::minLength($this->indexName, 3);
        Assert::maxLength($this->indexName, 255);
        Assert::regex($this->indexName, '/^[a-zA-Z0-9_.\-]+$/');
        Assert::minCount($this->searchVector, 1);
        Assert::maxCount($this->searchVector, 4096);
        Assert::stringNotEmpty($this->tableName);
        Assert::maxLength($this->tableName, 1024);
        Assert::positiveInteger($this->topK);
        Assert::nullOrStringNotEmpty($this->projectionExpression);
        Assert::nullOrStringNotEmpty($this->searchConditionExpression);
    }

    /**
     * Searches for the items nearest to a vector given as plain numbers, which convert to `N` values the way
     * {@see AttributeValue::number()} converts them.
     *
     * @param non-empty-string $indexName the vector index to search
     * @param array<float|int|string> $searchVector one number per dimension of the index
     * @param non-empty-string $tableName the table name or its ARN
     * @param positive-int $topK the number of most similar items to return
     * @param null|non-empty-string $projectionExpression
     * @param null|non-empty-string $searchConditionExpression
     * @throws InvalidArgumentException
     */
    public static function nearest(
        string $indexName,
        array $searchVector,
        string $tableName,
        int $topK,
        ?NonEmptyStringMap $expressionAttributeNames = null,
        ?AttributeValueMap $expressionAttributeValues = null,
        ?string $projectionExpression = null,
        ?ReturnConsumedCapacity $returnConsumedCapacity = null,
        ?string $searchConditionExpression = null,
    ): self {
        return new self(
            indexName: $indexName,
            searchVector: new AttributeValueList(array_map(AttributeValue::number(...), array_values($searchVector))),
            tableName: $tableName,
            topK: $topK,
            expressionAttributeNames: $expressionAttributeNames,
            expressionAttributeValues: $expressionAttributeValues,
            projectionExpression: $projectionExpression,
            returnConsumedCapacity: $returnConsumedCapacity,
            searchConditionExpression: $searchConditionExpression,
        );
    }
}
