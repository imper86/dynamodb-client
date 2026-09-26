<?php

declare(strict_types=1);

namespace Imper86\DynamoDBClient\Message;

use Imper86\DynamoDBClient\Model\ConsumedCapacityList;
use Imper86\DynamoDBClient\Model\ItemListMap;
use Imper86\DynamoDBClient\Model\KeysAndAttributesMap;

final class BatchGetItemResponse
{
    /**
     * A table whose items were all read answers with an empty `UnprocessedKeys` map rather than
     * omitting the element, so both maps default to empty instead of to null.
     */
    public function __construct(
        public readonly ItemListMap $responses = new ItemListMap(),
        public readonly KeysAndAttributesMap $unprocessedKeys = new KeysAndAttributesMap(),
        public readonly ?ConsumedCapacityList $consumedCapacity = null,
    ) {}
}
