<?php

declare(strict_types=1);

namespace Imper86\DynamoDBClient\Model;

use Imper86\DynamoDBClient\ValueObject\AbstractObjectList;

/**
 * @extends AbstractObjectList<LocalSecondaryIndexInfo>
 */
final readonly class LocalSecondaryIndexInfoList extends AbstractObjectList
{
    /**
     * @return class-string<LocalSecondaryIndexInfo>
     */
    public static function itemType(): string
    {
        return LocalSecondaryIndexInfo::class;
    }
}
