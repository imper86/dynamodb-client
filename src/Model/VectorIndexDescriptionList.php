<?php

declare(strict_types=1);

namespace Imper86\DynamoDBClient\Model;

use Imper86\DynamoDBClient\ValueObject\AbstractObjectList;

/**
 * @extends AbstractObjectList<VectorIndexDescription>
 */
final class VectorIndexDescriptionList extends AbstractObjectList
{
    /**
     * @return class-string<VectorIndexDescription>
     */
    public static function itemType(): string
    {
        return VectorIndexDescription::class;
    }
}
