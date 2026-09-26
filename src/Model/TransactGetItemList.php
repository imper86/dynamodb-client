<?php

declare(strict_types=1);

namespace Imper86\DynamoDBClient\Model;

use Imper86\DynamoDBClient\ValueObject\AbstractObjectList;

/**
 * @extends AbstractObjectList<TransactGetItem>
 */
final class TransactGetItemList extends AbstractObjectList
{
    /**
     * @return class-string<TransactGetItem>
     */
    public static function itemType(): string
    {
        return TransactGetItem::class;
    }
}
