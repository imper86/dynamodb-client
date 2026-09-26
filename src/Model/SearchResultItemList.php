<?php

declare(strict_types=1);

namespace Imper86\DynamoDBClient\Model;

use Imper86\DynamoDBClient\ValueObject\AbstractObjectList;

/**
 * @extends AbstractObjectList<SearchResultItem>
 */
final class SearchResultItemList extends AbstractObjectList
{
    /**
     * @return class-string<SearchResultItem>
     */
    public static function itemType(): string
    {
        return SearchResultItem::class;
    }
}
