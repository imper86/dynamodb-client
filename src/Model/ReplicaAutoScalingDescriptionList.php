<?php

declare(strict_types=1);

namespace Imper86\DynamoDBClient\Model;

use Imper86\DynamoDBClient\ValueObject\AbstractObjectList;

/**
 * @extends AbstractObjectList<ReplicaAutoScalingDescription>
 */
final readonly class ReplicaAutoScalingDescriptionList extends AbstractObjectList
{
    /**
     * @return class-string<ReplicaAutoScalingDescription>
     */
    public static function itemType(): string
    {
        return ReplicaAutoScalingDescription::class;
    }
}
