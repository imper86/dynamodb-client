<?php

declare(strict_types=1);

namespace Imper86\DynamoDBClient\Model;

use Imper86\DynamoDBClient\ValueObject\AbstractObjectList;

/**
 * @extends AbstractObjectList<TransactWriteItem>
 */
final readonly class TransactWriteItemList extends AbstractObjectList
{
    /**
     * @return class-string<TransactWriteItem>
     */
    public static function itemType(): string
    {
        return TransactWriteItem::class;
    }
}
