<?php

declare(strict_types=1);

namespace Imper86\DynamoDBClient\Model;

use Imper86\DynamoDBClient\ValueObject\AbstractObjectList;

/**
 * @extends AbstractObjectList<GlobalSecondaryIndex>
 */
final class GlobalSecondaryIndexList extends AbstractObjectList
{
    /**
     * @return class-string<GlobalSecondaryIndex>
     */
    public static function itemType(): string
    {
        return GlobalSecondaryIndex::class;
    }
}
