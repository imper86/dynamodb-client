<?php

declare(strict_types=1);

namespace Imper86\DynamoDBClient\Model;

use Imper86\DynamoDBClient\ValueObject\AbstractObjectList;

/**
 * @extends AbstractObjectList<ReplicaGlobalSecondaryIndexDescription>
 */
final class ReplicaGlobalSecondaryIndexDescriptionList extends AbstractObjectList
{
    /**
     * @return class-string<ReplicaGlobalSecondaryIndexDescription>
     */
    public static function itemType(): string
    {
        return ReplicaGlobalSecondaryIndexDescription::class;
    }
}
