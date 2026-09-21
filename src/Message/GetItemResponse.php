<?php

declare(strict_types=1);

namespace Imper86\DynamoDBClient\Message;

use Imper86\DynamoDBClient\Model\AttributeValueMap;
use Imper86\DynamoDBClient\Model\ConsumedCapacity;

final readonly class GetItemResponse
{
    /**
     * @param null|AttributeValueMap $item the attributes of the matching item, or null when the key matches none
     */
    public function __construct(
        public ?AttributeValueMap $item = null,
        public ?ConsumedCapacity $consumedCapacity = null,
    ) {}
}
