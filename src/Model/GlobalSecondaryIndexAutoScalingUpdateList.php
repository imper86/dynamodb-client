<?php

declare(strict_types=1);

namespace Imper86\DynamoDBClient\Model;

use Imper86\DynamoDBClient\ValueObject\AbstractObjectList;

/**
 * @extends AbstractObjectList<GlobalSecondaryIndexAutoScalingUpdate>
 */
final class GlobalSecondaryIndexAutoScalingUpdateList extends AbstractObjectList
{
    /**
     * @return class-string<GlobalSecondaryIndexAutoScalingUpdate>
     */
    public static function itemType(): string
    {
        return GlobalSecondaryIndexAutoScalingUpdate::class;
    }
}
