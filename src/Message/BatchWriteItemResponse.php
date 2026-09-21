<?php

declare(strict_types=1);

namespace Imper86\DynamoDBClient\Message;

use Imper86\DynamoDBClient\Model\ConsumedCapacityList;
use Imper86\DynamoDBClient\Model\ItemCollectionMetricsListMap;
use Imper86\DynamoDBClient\Model\WriteRequestListMap;

final readonly class BatchWriteItemResponse
{
    /**
     * A fully processed batch answers with an empty `UnprocessedItems` map rather than omitting the
     * element, so it defaults to empty instead of to null. `ItemCollectionMetrics` only comes back
     * when the request asked for it.
     */
    public function __construct(
        public WriteRequestListMap $unprocessedItems = new WriteRequestListMap(),
        public ?ItemCollectionMetricsListMap $itemCollectionMetrics = null,
        public ?ConsumedCapacityList $consumedCapacity = null,
    ) {}
}
