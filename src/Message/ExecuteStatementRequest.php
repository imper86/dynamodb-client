<?php

declare(strict_types=1);

namespace Imper86\DynamoDBClient\Message;

use InvalidArgumentException;
use Imper86\DynamoDBClient\Model\AttributeValueList;
use Imper86\DynamoDBClient\Model\ReturnConsumedCapacity;
use Imper86\DynamoDBClient\Model\ReturnValuesOnConditionCheckFailure;
use Webmozart\Assert\Assert;

final class ExecuteStatementRequest
{
    /**
     * @param non-empty-string $statement the PartiQL statement to run
     * @param null|positive-int $limit the maximum number of items to evaluate, not to return
     * @param null|non-empty-string $nextToken the token a previous response returned, to read the next page
     * @throws InvalidArgumentException
     */
    public function __construct(
        public readonly string $statement,
        public readonly ?bool $consistentRead = null,
        public readonly ?int $limit = null,
        public readonly ?string $nextToken = null,
        public readonly ?AttributeValueList $parameters = null,
        public readonly ?ReturnConsumedCapacity $returnConsumedCapacity = null,
        public readonly ?ReturnValuesOnConditionCheckFailure $returnValuesOnConditionCheckFailure = null,
    ) {
        Assert::stringNotEmpty($this->statement);
        Assert::maxLength($this->statement, 8192);
        Assert::nullOrPositiveInteger($this->limit);
        Assert::nullOrStringNotEmpty($this->nextToken);
        Assert::nullOrMaxLength($this->nextToken, 32768);
        Assert::nullOrMinCount($this->parameters, 1);
    }
}
