<?php

declare(strict_types=1);

namespace Imper86\DynamoDBClient\Model;

use Imper86\DynamoDBClient\ValueObject\AbstractObjectList;

/**
 * @extends AbstractObjectList<VectorIndexInfo>
 */
final class VectorIndexInfoList extends AbstractObjectList
{
    /**
     * @return class-string<VectorIndexInfo>
     */
    public static function itemType(): string
    {
        return VectorIndexInfo::class;
    }
}
