<?php

declare(strict_types=1);

namespace Imper86\DynamoDBClient\Message;

use Imper86\DynamoDBClient\Model\AttributeValueMap;
use Imper86\DynamoDBClient\Model\ConsumedCapacity;
use Imper86\DynamoDBClient\Model\ItemCollectionMetrics;

final readonly class PutItemResponse
{
    /**
     * @param null|AttributeValueMap $attributes the item the put replaced, only when the request asked for
     *                                           `ALL_OLD` and an item with the same key existed
     */
    public function __construct(
        public ?AttributeValueMap $attributes = null,
        public ?ItemCollectionMetrics $itemCollectionMetrics = null,
        public ?ConsumedCapacity $consumedCapacity = null,
    ) {}
}
