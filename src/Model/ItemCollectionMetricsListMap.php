<?php

declare(strict_types=1);

namespace Imper86\DynamoDBClient\Model;

use Imper86\DynamoDBClient\ValueObject\AbstractObjectMap;

/**
 * The item collection metrics of each table, keyed by table name or table ARN.
 *
 * @extends AbstractObjectMap<ItemCollectionMetricsList>
 */
final class ItemCollectionMetricsListMap extends AbstractObjectMap
{
    /**
     * @return class-string<ItemCollectionMetricsList>
     */
    public static function itemType(): string
    {
        return ItemCollectionMetricsList::class;
    }
}
