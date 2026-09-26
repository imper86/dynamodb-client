<?php

declare(strict_types=1);

namespace Imper86\DynamoDBClient\Model;

use Imper86\DynamoDBClient\ValueObject\AbstractObjectList;

/**
 * The metrics of every item collection a single table's writes touched.
 *
 * @extends AbstractObjectList<ItemCollectionMetrics>
 */
final class ItemCollectionMetricsList extends AbstractObjectList
{
    /**
     * @return class-string<ItemCollectionMetrics>
     */
    public static function itemType(): string
    {
        return ItemCollectionMetrics::class;
    }
}
