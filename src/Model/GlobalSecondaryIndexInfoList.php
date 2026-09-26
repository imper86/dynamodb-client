<?php

declare(strict_types=1);

namespace Imper86\DynamoDBClient\Model;

use Imper86\DynamoDBClient\ValueObject\AbstractObjectList;

/**
 * @extends AbstractObjectList<GlobalSecondaryIndexInfo>
 */
final class GlobalSecondaryIndexInfoList extends AbstractObjectList
{
    /**
     * @return class-string<GlobalSecondaryIndexInfo>
     */
    public static function itemType(): string
    {
        return GlobalSecondaryIndexInfo::class;
    }
}
