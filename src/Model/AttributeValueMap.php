<?php

declare(strict_types=1);

namespace Imper86\DynamoDBClient\Model;

use Imper86\DynamoDBClient\ValueObject\AbstractObjectMap;

/**
 * @extends AbstractObjectMap<AttributeValue>
 */
final class AttributeValueMap extends AbstractObjectMap
{
    /**
     * @return class-string<AttributeValue>
     */
    public static function itemType(): string
    {
        return AttributeValue::class;
    }
}
