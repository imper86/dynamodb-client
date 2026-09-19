<?php

declare(strict_types=1);

namespace OoAws\DynamoDBClient\Model;

use OoAws\DynamoDBClient\ValueObject\AbstractObjectMap;

/**
 * @extends AbstractObjectMap<ConsumedCapacity>
 */
final readonly class ConsumedCapacityObjectMap extends AbstractObjectMap
{
    /**
     * @return class-string<ConsumedCapacity>
     */
    public static function itemType(): string
    {
        return ConsumedCapacity::class;
    }
}
