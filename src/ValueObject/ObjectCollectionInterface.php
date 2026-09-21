<?php

declare(strict_types=1);

namespace Imper86\DynamoDBClient\ValueObject;

/**
 * @template TKey of array-key
 * @template-covariant T of object
 * @extends CollectionInterface<TKey, T>
 */
interface ObjectCollectionInterface extends CollectionInterface
{
    /**
     * @return class-string<T>
     */
    public static function itemType(): string;
}
