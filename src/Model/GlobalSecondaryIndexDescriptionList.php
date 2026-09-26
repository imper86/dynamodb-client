<?php

declare(strict_types=1);

namespace Imper86\DynamoDBClient\Model;

use Imper86\DynamoDBClient\ValueObject\AbstractObjectList;

/**
 * @extends AbstractObjectList<GlobalSecondaryIndexDescription>
 */
final class GlobalSecondaryIndexDescriptionList extends AbstractObjectList
{
    /**
     * @return class-string<GlobalSecondaryIndexDescription>
     */
    public static function itemType(): string
    {
        return GlobalSecondaryIndexDescription::class;
    }
}
