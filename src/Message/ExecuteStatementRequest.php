<?php

declare(strict_types=1);

namespace Imper86\DynamoDBClient\Message;

use InvalidArgumentException;
use Imper86\DynamoDBClient\Model\AttributeValueList;
use Imper86\DynamoDBClient\Model\ReturnConsumedCapacity;
use Imper86\DynamoDBClient\Model\ReturnValuesOnConditionCheckFailure;
use Webmozart\Assert\Assert;

final readonly class ExecuteStatementRequest
{
    /**
     * @param non-empty-string $statement the PartiQL statement to run
     * @param null|positive-int $limit the maximum number of items to evaluate, not to return
     * @param null|non-empty-string $nextToken the token a previous response returned, to read the next page
     * @throws InvalidArgumentException
     */
    public function __construct(
        public string $statement,
        public ?bool $consistentRead = null,
        public ?int $limit = null,
        public ?string $nextToken = null,
        public ?AttributeValueList $parameters = null,
        public ?ReturnConsumedCapacity $returnConsumedCapacity = null,
        public ?ReturnValuesOnConditionCheckFailure $returnValuesOnConditionCheckFailure = null,
    ) {
        Assert::stringNotEmpty($this->statement);
        Assert::maxLength($this->statement, 8192);
        Assert::nullOrPositiveInteger($this->limit);
        Assert::nullOrStringNotEmpty($this->nextToken);
        Assert::nullOrMaxLength($this->nextToken, 32768);
        Assert::nullOrMinCount($this->parameters, 1);
    }
}
