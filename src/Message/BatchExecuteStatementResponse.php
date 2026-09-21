<?php

declare(strict_types=1);

namespace Imper86\DynamoDBClient\Message;

use Imper86\DynamoDBClient\Model\BatchStatementResponseList;
use Imper86\DynamoDBClient\Model\ConsumedCapacityList;

final readonly class BatchExecuteStatementResponse
{
    /**
     * The service always answers a successful batch with one response per statement, but it does not
     * promise the element: an absent `Responses` becomes an empty list rather than a failed deserialization.
     */
    public function __construct(
        public BatchStatementResponseList $responses = new BatchStatementResponseList(),
        public ?ConsumedCapacityList $consumedCapacity = null,
    ) {}
}
