<?php

declare(strict_types=1);

namespace Imper86\DynamoDBClient\Message;

use Imper86\DynamoDBClient\Model\AttributeValueMap;
use Imper86\DynamoDBClient\Model\ConsumedCapacity;

final readonly class GetItemResponse
{
    public function __construct(
        public ConsumedCapacity $consumedCapacity,
        public AttributeValueMap $item,
    ) {}
}
