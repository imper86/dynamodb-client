<?php

declare(strict_types=1);

namespace Imper86\DynamoDBClient\Model;

use Imper86\DynamoDBClient\ValueObject\AbstractObjectList;

/**
 * @extends AbstractObjectList<VectorIndexUpdate>
 */
final readonly class VectorIndexUpdateList extends AbstractObjectList
{
    /**
     * @return class-string<VectorIndexUpdate>
     */
    public static function itemType(): string
    {
        return VectorIndexUpdate::class;
    }
}
