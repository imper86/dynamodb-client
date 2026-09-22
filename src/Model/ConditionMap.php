<?php

declare(strict_types=1);

namespace Imper86\DynamoDBClient\Model;

use Imper86\DynamoDBClient\ValueObject\AbstractObjectMap;

/**
 * @extends AbstractObjectMap<Condition>
 */
final readonly class ConditionMap extends AbstractObjectMap
{
    /**
     * @return class-string<Condition>
     */
    public static function itemType(): string
    {
        return Condition::class;
    }
}
