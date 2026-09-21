<?php

declare(strict_types=1);

namespace Imper86\DynamoDBClient\Model;

use Imper86\DynamoDBClient\ValueObject\AbstractObjectList;

/**
 * The primary keys of the items to read, one map of key attributes per item.
 *
 * @extends AbstractObjectList<AttributeValueMap>
 */
final readonly class KeyList extends AbstractObjectList
{
    /**
     * @return class-string<AttributeValueMap>
     */
    public static function itemType(): string
    {
        return AttributeValueMap::class;
    }
}
