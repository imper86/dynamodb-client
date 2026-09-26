<?php

declare(strict_types=1);

namespace Imper86\DynamoDBClient\Model;

use Imper86\DynamoDBClient\ValueObject\AbstractObjectList;

/**
 * @extends AbstractObjectList<AttributeValue>
 */
final class AttributeValueList extends AbstractObjectList
{
    /**
     * @return class-string<AttributeValue>
     */
    public static function itemType(): string
    {
        return AttributeValue::class;
    }
}
