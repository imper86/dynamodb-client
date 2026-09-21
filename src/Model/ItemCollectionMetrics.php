<?php

declare(strict_types=1);

namespace Imper86\DynamoDBClient\Model;

use Imper86\DynamoDBClient\ValueObject\DoubleList;

final readonly class ItemCollectionMetrics
{
    /**
     * @param null|DoubleList $sizeEstimateRangeGB the lower and upper bound of the item collection's size, in GB
     */
    public function __construct(
        public ?AttributeValueMap $itemCollectionKey = null,
        public ?DoubleList $sizeEstimateRangeGB = null,
    ) {}
}
