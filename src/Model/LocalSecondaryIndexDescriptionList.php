<?php

declare(strict_types=1);

namespace Imper86\DynamoDBClient\Model;

use Imper86\DynamoDBClient\ValueObject\AbstractObjectList;

/**
 * @extends AbstractObjectList<LocalSecondaryIndexDescription>
 */
final readonly class LocalSecondaryIndexDescriptionList extends AbstractObjectList
{
    /**
     * @return class-string<LocalSecondaryIndexDescription>
     */
    public static function itemType(): string
    {
        return LocalSecondaryIndexDescription::class;
    }
}
