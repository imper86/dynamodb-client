<?php

declare(strict_types=1);

namespace Imper86\DynamoDBClient\Message;

use Imper86\DynamoDBClient\Model\ConsumedCapacityList;
use Imper86\DynamoDBClient\Model\ItemResponseList;

final class TransactGetItemsResponse
{
    /**
     * @param ItemResponseList $responses one response per requested item, in request order; the item is
     *                                    null for one that could not be retrieved
     * @param null|ConsumedCapacityList $consumedCapacity the capacity consumed in each table read
     */
    public function __construct(
        public readonly ItemResponseList $responses = new ItemResponseList(),
        public readonly ?ConsumedCapacityList $consumedCapacity = null,
    ) {}
}
