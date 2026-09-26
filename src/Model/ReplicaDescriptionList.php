<?php

declare(strict_types=1);

namespace Imper86\DynamoDBClient\Model;

use Imper86\DynamoDBClient\ValueObject\AbstractObjectList;

/**
 * @extends AbstractObjectList<ReplicaDescription>
 */
final class ReplicaDescriptionList extends AbstractObjectList
{
    /**
     * @return class-string<ReplicaDescription>
     */
    public static function itemType(): string
    {
        return ReplicaDescription::class;
    }
}
