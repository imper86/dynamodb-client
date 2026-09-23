<?php

declare(strict_types=1);

namespace Imper86\DynamoDBClient\Message;

use Imper86\DynamoDBClient\Model\ConsumedCapacityList;
use Imper86\DynamoDBClient\Model\ItemCollectionMetricsListMap;

final readonly class TransactWriteItemsResponse
{
    /**
     * Both elements only come back when the request asked for them.
     *
     * @param null|ItemCollectionMetricsListMap $itemCollectionMetrics the item collections each table's actions
     *                                                                 affected, by table name
     * @param null|ConsumedCapacityList $consumedCapacity the capacity consumed, in the order of the actions
     */
    public function __construct(
        public ?ItemCollectionMetricsListMap $itemCollectionMetrics = null,
        public ?ConsumedCapacityList $consumedCapacity = null,
    ) {}
}
