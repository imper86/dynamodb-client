<?php

declare(strict_types=1);

namespace Imper86\DynamoDBClient\Model;

use Imper86\DynamoDBClient\ValueObject\AbstractObjectMap;

/**
 * @extends AbstractObjectMap<ExpectedAttributeValue>
 */
final readonly class ExpectedAttributeValueMap extends AbstractObjectMap
{
    /**
     * @return class-string<ExpectedAttributeValue>
     */
    public static function itemType(): string
    {
        return ExpectedAttributeValue::class;
    }
}
