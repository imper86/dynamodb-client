<?php

declare(strict_types=1);

namespace Imper86\DynamoDBClient\Message;

use Imper86\DynamoDBClient\Model\AttributeValueMap;
use Imper86\DynamoDBClient\Model\ConsumedCapacity;
use Imper86\DynamoDBClient\Model\ItemCollectionMetrics;

final readonly class UpdateItemResponse
{
    /**
     * @param null|AttributeValueMap $attributes the item, or its updated attributes, before or after the update,
     *                                           only when the request asked for anything but `NONE`
     */
    public function __construct(
        public ?AttributeValueMap $attributes = null,
        public ?ItemCollectionMetrics $itemCollectionMetrics = null,
        public ?ConsumedCapacity $consumedCapacity = null,
    ) {}
}
