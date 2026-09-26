<?php

declare(strict_types=1);

namespace Imper86\DynamoDBClient\Message;

use Imper86\DynamoDBClient\Model\ConsumedCapacityList;
use Imper86\DynamoDBClient\Model\ItemResponseList;

final class ExecuteTransactionResponse
{
    /**
     * @param ItemResponseList $responses one response per statement of a read transaction, in statement
     *                                    order; empty for a write transaction
     * @param null|ConsumedCapacityList $consumedCapacity the capacity each statement consumed, in statement order
     */
    public function __construct(
        public readonly ItemResponseList $responses = new ItemResponseList(),
        public readonly ?ConsumedCapacityList $consumedCapacity = null,
    ) {}
}
