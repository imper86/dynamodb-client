<?php

declare(strict_types=1);

namespace OoAws\DynamoDBClient\Model;

use OoAws\DynamoDBClient\ValueObject\AbstractObjectMap;

/**
 * @extends AbstractObjectMap<AttributeValue>
 */
final readonly class AttributeValueMap extends AbstractObjectMap
{
    /**
     * @return class-string<AttributeValue>
     */
    public static function itemType(): string
    {
        return AttributeValue::class;
    }
}
