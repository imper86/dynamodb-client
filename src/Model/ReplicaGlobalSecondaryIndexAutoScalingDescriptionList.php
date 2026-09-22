<?php

declare(strict_types=1);

namespace Imper86\DynamoDBClient\Model;

use Imper86\DynamoDBClient\ValueObject\AbstractObjectList;

/**
 * @extends AbstractObjectList<ReplicaGlobalSecondaryIndexAutoScalingDescription>
 */
final readonly class ReplicaGlobalSecondaryIndexAutoScalingDescriptionList extends AbstractObjectList
{
    /**
     * @return class-string<ReplicaGlobalSecondaryIndexAutoScalingDescription>
     */
    public static function itemType(): string
    {
        return ReplicaGlobalSecondaryIndexAutoScalingDescription::class;
    }
}
