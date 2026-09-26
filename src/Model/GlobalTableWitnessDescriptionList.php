<?php

declare(strict_types=1);

namespace Imper86\DynamoDBClient\Model;

use Imper86\DynamoDBClient\ValueObject\AbstractObjectList;

/**
 * @extends AbstractObjectList<GlobalTableWitnessDescription>
 */
final class GlobalTableWitnessDescriptionList extends AbstractObjectList
{
    /**
     * @return class-string<GlobalTableWitnessDescription>
     */
    public static function itemType(): string
    {
        return GlobalTableWitnessDescription::class;
    }
}
