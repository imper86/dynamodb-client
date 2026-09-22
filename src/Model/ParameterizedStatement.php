<?php

declare(strict_types=1);

namespace Imper86\DynamoDBClient\Model;

use InvalidArgumentException;
use Webmozart\Assert\Assert;

final readonly class ParameterizedStatement
{
    /**
     * @param non-empty-string $statement
     * @throws InvalidArgumentException
     */
    public function __construct(
        public string $statement,
        public ?AttributeValueList $parameters = null,
        public ?ReturnValuesOnConditionCheckFailure $returnValuesOnConditionCheckFailure = null,
    ) {
        Assert::stringNotEmpty($this->statement);
        Assert::maxLength($this->statement, 8192);
        Assert::nullOrMinCount($this->parameters, 1);
    }
}
