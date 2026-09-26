<?php

declare(strict_types=1);

namespace Imper86\DynamoDBClient\Model;

use Imper86\DynamoDBClient\ValueObject\DoubleList;

final class ItemCollectionMetrics
{
    /**
     * @param null|DoubleList $sizeEstimateRangeGB the lower and upper bound of the item collection's size, in GB
     */
    public function __construct(
        public readonly ?AttributeValueMap $itemCollectionKey = null,
        public readonly ?DoubleList $sizeEstimateRangeGB = null,
    ) {}
}
