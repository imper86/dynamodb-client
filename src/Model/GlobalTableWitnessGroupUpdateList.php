<?php

declare(strict_types=1);

namespace Imper86\DynamoDBClient\Model;

use Imper86\DynamoDBClient\ValueObject\AbstractObjectList;

/**
 * @extends AbstractObjectList<GlobalTableWitnessGroupUpdate>
 */
final readonly class GlobalTableWitnessGroupUpdateList extends AbstractObjectList
{
    /**
     * @return class-string<GlobalTableWitnessGroupUpdate>
     */
    public static function itemType(): string
    {
        return GlobalTableWitnessGroupUpdate::class;
    }
}
