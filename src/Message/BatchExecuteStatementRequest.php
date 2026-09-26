<?php

declare(strict_types=1);

namespace Imper86\DynamoDBClient\Message;

use InvalidArgumentException;
use Imper86\DynamoDBClient\Model\BatchStatementRequestList;
use Imper86\DynamoDBClient\Model\ReturnConsumedCapacity;
use Webmozart\Assert\Assert;

final class BatchExecuteStatementRequest
{
    /**
     * @throws InvalidArgumentException
     */
    public function __construct(
        public readonly BatchStatementRequestList $statements,
        public readonly ?ReturnConsumedCapacity $returnConsumedCapacity = null,
    ) {
        Assert::minCount($this->statements, 1);
        Assert::maxCount($this->statements, 25);
    }
}
