<?php

declare(strict_types=1);

namespace Imper86\DynamoDBClient\Model;

use Imper86\DynamoDBClient\ValueObject\AbstractObjectList;

/**
 * @extends AbstractObjectList<ReplicaAutoScalingUpdate>
 */
final readonly class ReplicaAutoScalingUpdateList extends AbstractObjectList
{
    /**
     * @return class-string<ReplicaAutoScalingUpdate>
     */
    public static function itemType(): string
    {
        return ReplicaAutoScalingUpdate::class;
    }
}
