<?php

declare(strict_types=1);

namespace Imper86\DynamoDBClient\Model;

use Imper86\DynamoDBClient\ValueObject\AbstractObjectList;

/**
 * The partition key and, optionally, the sort key of a table or index.
 *
 * @extends AbstractObjectList<KeySchemaElement>
 */
final class KeySchemaElementList extends AbstractObjectList
{
    /**
     * @return class-string<KeySchemaElement>
     */
    public static function itemType(): string
    {
        return KeySchemaElement::class;
    }
}
