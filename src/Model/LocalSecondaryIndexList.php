<?php

declare(strict_types=1);

namespace Imper86\DynamoDBClient\Model;

use Imper86\DynamoDBClient\ValueObject\AbstractObjectList;

/**
 * @extends AbstractObjectList<LocalSecondaryIndex>
 */
final readonly class LocalSecondaryIndexList extends AbstractObjectList
{
    /**
     * @return class-string<LocalSecondaryIndex>
     */
    public static function itemType(): string
    {
        return LocalSecondaryIndex::class;
    }
}
