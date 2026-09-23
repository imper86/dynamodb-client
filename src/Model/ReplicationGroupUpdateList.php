<?php

declare(strict_types=1);

namespace Imper86\DynamoDBClient\Model;

use Imper86\DynamoDBClient\ValueObject\AbstractObjectList;

/**
 * @extends AbstractObjectList<ReplicationGroupUpdate>
 */
final readonly class ReplicationGroupUpdateList extends AbstractObjectList
{
    /**
     * @return class-string<ReplicationGroupUpdate>
     */
    public static function itemType(): string
    {
        return ReplicationGroupUpdate::class;
    }
}
