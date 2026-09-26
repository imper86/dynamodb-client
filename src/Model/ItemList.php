<?php

declare(strict_types=1);

namespace Imper86\DynamoDBClient\Model;

use Imper86\DynamoDBClient\ValueObject\AbstractObjectList;

/**
 * The items a single table answered with, one map of attributes per item.
 *
 * @extends AbstractObjectList<AttributeValueMap>
 */
final class ItemList extends AbstractObjectList
{
    /**
     * @return class-string<AttributeValueMap>
     */
    public static function itemType(): string
    {
        return AttributeValueMap::class;
    }
}
