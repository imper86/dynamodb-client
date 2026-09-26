<?php

declare(strict_types=1);

namespace Imper86\DynamoDBClient\Model;

use Imper86\DynamoDBClient\ValueObject\AbstractObjectList;

/**
 * @extends AbstractObjectList<SearchSchemaElement>
 */
final class SearchSchemaElementList extends AbstractObjectList
{
    /**
     * @return class-string<SearchSchemaElement>
     */
    public static function itemType(): string
    {
        return SearchSchemaElement::class;
    }
}
