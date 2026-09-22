<?php

declare(strict_types=1);

namespace Imper86\DynamoDBClient\Message;

use Imper86\DynamoDBClient\Model\AttributeValueMap;
use Imper86\DynamoDBClient\Model\ConsumedCapacity;
use Imper86\DynamoDBClient\Model\ItemList;

final readonly class QueryResponse
{
    /**
     * A query that matches nothing, or one that asks for `COUNT`, answers without `Items`, so they default
     * to empty instead of to null.
     *
     * @param ItemList $items the matching items, after any filter
     * @param null|int $count the number of matching items, after any filter
     * @param null|int $scannedCount the number of items evaluated, before any filter
     * @param null|AttributeValueMap $lastEvaluatedKey where the next page starts; absent on the last page
     */
    public function __construct(
        public ItemList $items = new ItemList(),
        public ?int $count = null,
        public ?int $scannedCount = null,
        public ?AttributeValueMap $lastEvaluatedKey = null,
        public ?ConsumedCapacity $consumedCapacity = null,
    ) {}
}
