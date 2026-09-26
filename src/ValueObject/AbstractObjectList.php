<?php

declare(strict_types=1);

namespace Imper86\DynamoDBClient\ValueObject;

use Webmozart\Assert\Assert;

/**
 * @template T of object
 * @extends AbstractList<T>
 * @implements ObjectCollectionInterface<int, T>
 */
abstract class AbstractObjectList extends AbstractList implements ObjectCollectionInterface
{
    final protected static function validate(array $items): void
    {
        Assert::allIsInstanceOf($items, static::itemType());
    }
}
