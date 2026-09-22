<?php

declare(strict_types=1);

namespace Imper86\DynamoDBClient\Model;

use Imper86\DynamoDBClient\ValueObject\AbstractObjectList;

/**
 * @extends AbstractObjectList<Tag>
 */
final readonly class TagList extends AbstractObjectList
{
    /**
     * @return class-string<Tag>
     */
    public static function itemType(): string
    {
        return Tag::class;
    }
}
