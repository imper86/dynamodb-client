<?php

declare(strict_types=1);

namespace Imper86\DynamoDBClient\Model;

use Imper86\DynamoDBClient\ValueObject\AbstractObjectMap;

/**
 * @extends AbstractObjectMap<ConsumedCapacity>
 */
final class ConsumedCapacityMap extends AbstractObjectMap
{
    /**
     * @return class-string<ConsumedCapacity>
     */
    public static function itemType(): string
    {
        return ConsumedCapacity::class;
    }
}
