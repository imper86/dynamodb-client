<?php

declare(strict_types=1);

namespace OoAws\DynamoDBClient\Model;

use OoAws\DynamoDBClient\ValueObject\AbstractObjectList;

/**
 * @extends AbstractObjectList<AttributeValue>
 */
final readonly class AttributeValueList extends AbstractObjectList
{
    /**
     * @return class-string<AttributeValue>
     */
    public static function itemType(): string
    {
        return AttributeValue::class;
    }
}
