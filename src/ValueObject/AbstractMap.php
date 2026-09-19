<?php

declare(strict_types=1);

namespace OoAws\DynamoDBClient\ValueObject;

use ArrayIterator;
use InvalidArgumentException;
use Webmozart\Assert\Assert;

use function array_key_exists;
use function array_keys;
use function count;

/**
 * @template T
 * @implements CollectionInterface<string, T>
 */
abstract readonly class AbstractMap implements CollectionInterface
{
    /**
     * @param array<string, T> $items
     * @throws InvalidArgumentException
     */
    final public function __construct(
        protected array $items = [],
    ) {
        Assert::isMap($this->items);
        static::validate($this->items);
    }

    /**
     * @return ArrayIterator<string, T>
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
     * @return array<string, T>
     */
    public function toArray(): array
    {
        return $this->items;
    }

    /**
     * @return null|T
     */
    public function get(string $key): mixed
    {
        return $this->items[$key] ?? null;
    }

    public function has(string $key): bool
    {
        return array_key_exists($key, $this->items);
    }

    /**
     * @return list<string>
     */
    public function keys(): array
    {
        return array_keys($this->items);
    }

    /**
     * @param array<string, mixed> $items
     * @throws InvalidArgumentException
     */
    abstract protected static function validate(array $items): void;
}
