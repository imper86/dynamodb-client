<?php

declare(strict_types=1);

namespace Imper86\DynamoDBClient\Model;

use Imper86\DynamoDBClient\ValueObject\AbstractObjectMap;

/**
 * The items each table answered with, keyed by table name or table ARN.
 *
 * @extends AbstractObjectMap<ItemList>
 */
final class ItemListMap extends AbstractObjectMap
{
    /**
     * @return class-string<ItemList>
     */
    public static function itemType(): string
    {
        return ItemList::class;
    }
}
