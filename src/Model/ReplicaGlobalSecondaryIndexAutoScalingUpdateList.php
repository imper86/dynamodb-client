<?php

declare(strict_types=1);

namespace Imper86\DynamoDBClient\Model;

use Imper86\DynamoDBClient\ValueObject\AbstractObjectList;

/**
 * @extends AbstractObjectList<ReplicaGlobalSecondaryIndexAutoScalingUpdate>
 */
final readonly class ReplicaGlobalSecondaryIndexAutoScalingUpdateList extends AbstractObjectList
{
    /**
     * @return class-string<ReplicaGlobalSecondaryIndexAutoScalingUpdate>
     */
    public static function itemType(): string
    {
        return ReplicaGlobalSecondaryIndexAutoScalingUpdate::class;
    }
}
