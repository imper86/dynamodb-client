<?php

declare(strict_types=1);

namespace Imper86\DynamoDBClient\ValueObject;

use Countable;
use IteratorAggregate;

/**
 * @template TKey of array-key
 * @template-covariant TValue
 * @extends IteratorAggregate<TKey, TValue>
 */
interface CollectionInterface extends IteratorAggregate, Countable
{
    /**
     * @return array<TKey, TValue>
     */
    public function toArray(): array;

    public function isEmpty(): bool;
}
