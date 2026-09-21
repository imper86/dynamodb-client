<?php

declare(strict_types=1);

namespace Imper86\DynamoDBClient\Model;

use Imper86\DynamoDBClient\ValueObject\AbstractObjectMap;

/**
 * The puts and deletes to perform on each table, keyed by table name or table ARN.
 *
 * @extends AbstractObjectMap<WriteRequestList>
 */
final readonly class WriteRequestListMap extends AbstractObjectMap
{
    /**
     * @return class-string<WriteRequestList>
     */
    public static function itemType(): string
    {
        return WriteRequestList::class;
    }
}
