<?php

declare(strict_types=1);

namespace Imper86\DynamoDBClient\Model;

use Imper86\DynamoDBClient\ValueObject\AbstractObjectList;

/**
 * The puts and deletes to perform on a single table.
 *
 * @extends AbstractObjectList<WriteRequest>
 */
final readonly class WriteRequestList extends AbstractObjectList
{
    /**
     * @return class-string<WriteRequest>
     */
    public static function itemType(): string
    {
        return WriteRequest::class;
    }
}
