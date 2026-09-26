<?php

declare(strict_types=1);

namespace Imper86\DynamoDBClient\Model;

use Imper86\DynamoDBClient\ValueObject\AbstractObjectList;

/**
 * @extends AbstractObjectList<VectorIndex>
 */
final class VectorIndexList extends AbstractObjectList
{
    /**
     * @return class-string<VectorIndex>
     */
    public static function itemType(): string
    {
        return VectorIndex::class;
    }
}
