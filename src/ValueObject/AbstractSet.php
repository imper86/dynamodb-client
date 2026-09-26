<?php

declare(strict_types=1);

namespace Imper86\DynamoDBClient\ValueObject;

use InvalidArgumentException;
use Webmozart\Assert\Assert;

/**
 * @template T
 * @extends AbstractList<T>
 */
abstract class AbstractSet extends AbstractList
{
    final protected static function validate(array $items): void
    {
        Assert::uniqueValues($items);
        static::validateItems($items);
    }

    /**
     * @param list<mixed> $items
     * @throws InvalidArgumentException
     */
    abstract protected static function validateItems(array $items): void;
}
