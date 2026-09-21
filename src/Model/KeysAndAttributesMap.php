<?php

declare(strict_types=1);

namespace Imper86\DynamoDBClient\Model;

use Imper86\DynamoDBClient\ValueObject\AbstractObjectMap;

/**
 * The items to read from each table, keyed by table name or table ARN.
 *
 * @extends AbstractObjectMap<KeysAndAttributes>
 */
final readonly class KeysAndAttributesMap extends AbstractObjectMap
{
    /**
     * @return class-string<KeysAndAttributes>
     */
    public static function itemType(): string
    {
        return KeysAndAttributes::class;
    }
}
