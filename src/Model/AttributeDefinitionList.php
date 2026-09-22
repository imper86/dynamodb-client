<?php

declare(strict_types=1);

namespace Imper86\DynamoDBClient\Model;

use Imper86\DynamoDBClient\ValueObject\AbstractObjectList;

/**
 * The attributes that make up the key schema of the table and of its indexes.
 *
 * @extends AbstractObjectList<AttributeDefinition>
 */
final readonly class AttributeDefinitionList extends AbstractObjectList
{
    /**
     * @return class-string<AttributeDefinition>
     */
    public static function itemType(): string
    {
        return AttributeDefinition::class;
    }
}
