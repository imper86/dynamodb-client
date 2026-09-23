<?php

declare(strict_types=1);

namespace Imper86\DynamoDBClient\Model;

use Imper86\DynamoDBClient\ValueObject\AbstractObjectList;

/**
 * @extends AbstractObjectList<GlobalSecondaryIndexUpdate>
 */
final readonly class GlobalSecondaryIndexUpdateList extends AbstractObjectList
{
    /**
     * @return class-string<GlobalSecondaryIndexUpdate>
     */
    public static function itemType(): string
    {
        return GlobalSecondaryIndexUpdate::class;
    }
}
