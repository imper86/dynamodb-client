<?php

declare(strict_types=1);

namespace Imper86\DynamoDBClient\Model;

use Imper86\DynamoDBClient\ValueObject\AbstractObjectList;

/**
 * @extends AbstractObjectList<ItemResponse>
 */
final readonly class ItemResponseList extends AbstractObjectList
{
    /**
     * @return class-string<ItemResponse>
     */
    public static function itemType(): string
    {
        return ItemResponse::class;
    }
}
