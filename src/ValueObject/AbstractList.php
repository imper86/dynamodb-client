<?php

declare(strict_types=1);

namespace OoAws\DynamoDBClient\ValueObject;

use ArrayIterator;
use InvalidArgumentException;
use Webmozart\Assert\Assert;

use function count;

/**
 * @template T
 * @implements CollectionInterface<int, T>
 */
abstract readonly class AbstractList implements CollectionInterface
{
    /**
     * @param list<T> $items
     * @throws InvalidArgumentException
     */
    final public function __construct(
        protected array $items = [],
    ) {
        Assert::isList($this->items);
        static::validate($this->items);
    }

    /**
     * @return ArrayIterator<int, T>
     */
    public function getIterator(): ArrayIterator
    {
        return new ArrayIterator($this->items);
    }

    public function count(): int
    {
        return count($this->items);
    }

    public function isEmpty(): bool
    {
        return [] === $this->items;
    }

    /**
     * @return list<T>
     */
    public function toArray(): array
    {
        return $this->items;
    }

    /**
     * @return null|T
     */
    public function get(int $index): mixed
    {
        return $this->items[$index] ?? null;
    }

    /**
     * @param list<mixed> $items
     * @throws InvalidArgumentException
     */
    abstract protected static function validate(array $items): void;
}
