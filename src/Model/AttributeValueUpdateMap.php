<?php

declare(strict_types=1);

namespace Imper86\DynamoDBClient\Model;

use Imper86\DynamoDBClient\ValueObject\AbstractObjectMap;

/**
 * @extends AbstractObjectMap<AttributeValueUpdate>
 */
final class AttributeValueUpdateMap extends AbstractObjectMap
{
    /**
     * @return class-string<AttributeValueUpdate>
     */
    public static function itemType(): string
    {
        return AttributeValueUpdate::class;
    }
}
