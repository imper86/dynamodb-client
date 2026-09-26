<?php

declare(strict_types=1);

namespace Imper86\DynamoDBClient\Model;

use Imper86\DynamoDBClient\ValueObject\AbstractObjectList;

/**
 * @extends AbstractObjectList<ReplicaGlobalSecondaryIndex>
 */
final class ReplicaGlobalSecondaryIndexList extends AbstractObjectList
{
    /**
     * @return class-string<ReplicaGlobalSecondaryIndex>
     */
    public static function itemType(): string
    {
        return ReplicaGlobalSecondaryIndex::class;
    }
}
