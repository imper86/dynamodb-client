<?php

declare(strict_types=1);

namespace OoAws\DynamoDBClient\Message;

use OoAws\DynamoDBClient\Model\AttributeValueMap;
use OoAws\DynamoDBClient\Model\ConsumedCapacity;

final readonly class GetItemResponse
{
    public function __construct(
        public ConsumedCapacity $consumedCapacity,
        public AttributeValueMap $item,
    ) {}
}
