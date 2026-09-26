<?php

declare(strict_types=1);

namespace Imper86\DynamoDBClient\Message;

use Imper86\DynamoDBClient\Model\AttributeValueMap;
use Imper86\DynamoDBClient\Model\ConsumedCapacity;
use Imper86\DynamoDBClient\Model\ItemCollectionMetrics;

final class UpdateItemResponse
{
    /**
     * @param null|AttributeValueMap $attributes the item, or its updated attributes, before or after the update,
     *                                           only when the request asked for anything but `NONE`
     */
    public function __construct(
        public readonly ?AttributeValueMap $attributes = null,
        public readonly ?ItemCollectionMetrics $itemCollectionMetrics = null,
        public readonly ?ConsumedCapacity $consumedCapacity = null,
    ) {}
}
