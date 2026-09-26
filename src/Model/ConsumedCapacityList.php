<?php

declare(strict_types=1);

namespace Imper86\DynamoDBClient\Model;

use Imper86\DynamoDBClient\ValueObject\AbstractObjectList;

/**
 * @extends AbstractObjectList<ConsumedCapacity>
 */
final class ConsumedCapacityList extends AbstractObjectList
{
    /**
     * @return class-string<ConsumedCapacity>
     */
    public static function itemType(): string
    {
        return ConsumedCapacity::class;
    }
}
