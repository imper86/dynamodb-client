<?php

declare(strict_types=1);

namespace OoAws\DynamoDBClient\ValueObject;

use Webmozart\Assert\Assert;

/**
 * @template T of object
 * @extends AbstractMap<T>
 * @implements ObjectCollectionInterface<string, T>
 */
abstract readonly class AbstractObjectMap extends AbstractMap implements ObjectCollectionInterface
{
    final protected static function validate(array $items): void
    {
        Assert::allIsInstanceOf($items, static::itemType());
    }
}
