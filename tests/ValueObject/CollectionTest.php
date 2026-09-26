<?php

declare(strict_types=1);

namespace Imper86\DynamoDBClientTests\ValueObject;

use InvalidArgumentException;
use Imper86\DynamoDBClient\Model\AttributeValue;
use Imper86\DynamoDBClient\Model\AttributeValueList;
use Imper86\DynamoDBClient\Model\AttributeValueMap;
use Imper86\DynamoDBClient\Model\Capacity;
use Imper86\DynamoDBClient\ValueObject\AbstractList;
use Imper86\DynamoDBClient\ValueObject\AbstractMap;
use Imper86\DynamoDBClient\ValueObject\AbstractSet;
use Imper86\DynamoDBClient\ValueObject\BlobSet;
use Imper86\DynamoDBClient\ValueObject\CollectionInterface;
use Imper86\DynamoDBClient\ValueObject\NonEmptyStringList;
use Imper86\DynamoDBClient\ValueObject\NonEmptyStringMap;
use Imper86\DynamoDBClient\ValueObject\NumberSet;
use Imper86\DynamoDBClient\ValueObject\StringList;
use Imper86\DynamoDBClient\ValueObject\StringSet;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

use function iterator_to_array;

/**
 * @internal
 */
#[CoversClass(AbstractList::class)]
#[CoversClass(AbstractMap::class)]
#[CoversClass(AbstractSet::class)]
#[CoversClass(AttributeValueList::class)]
#[CoversClass(AttributeValueMap::class)]
#[CoversClass(BlobSet::class)]
#[CoversClass(NonEmptyStringList::class)]
#[CoversClass(NonEmptyStringMap::class)]
#[CoversClass(NumberSet::class)]
#[CoversClass(StringList::class)]
#[CoversClass(StringSet::class)]
final class CollectionTest extends TestCase
{
    /**
     * @throws InvalidArgumentException
     */
    public function testList(): void
    {
        $first = new AttributeValue(string: 'a');
        $list = new AttributeValueList([$first, new AttributeValue(number: '1')]);

        self::assertCount(2, $list);
        self::assertFalse($list->isEmpty());
        self::assertSame($first, $list->get(0));
        self::assertNull($list->get(2));
        self::assertSame($list->toArray(), iterator_to_array($list));
    }

    /**
     * @throws InvalidArgumentException
     */
    public function testMap(): void
    {
        $value = new AttributeValue(string: 'a');
        $map = new AttributeValueMap(['id' => $value]);

        self::assertCount(1, $map);
        self::assertTrue($map->has('id'));
        self::assertSame($value, $map->get('id'));
        self::assertNull($map->get('missing'));
        self::assertSame(['id'], $map->keys());
        self::assertSame(['id' => $value], iterator_to_array($map));
        self::assertTrue((new AttributeValueMap())->isEmpty());
    }

    /**
     * @param class-string<CollectionInterface<array-key, mixed>> $class
     * @param array<mixed> $items
     */
    #[DataProvider('provideInvalidCollectionCases')]
    public function testInvalidCollection(string $class, array $items): void
    {
        $this->expectException(InvalidArgumentException::class);

        new $class($items);
    }

    /**
     * @return iterable<string, array{class-string<CollectionInterface<array-key, mixed>>, array<mixed>}>
     * @throws InvalidArgumentException
     */
    public static function provideInvalidCollectionCases(): iterable
    {
        yield 'object list with wrong item type' => [AttributeValueList::class, [new Capacity()]];

        yield 'object list with string keys' => [AttributeValueList::class, ['a' => new AttributeValue()]];

        yield 'object map with int keys' => [AttributeValueMap::class, [new AttributeValue()]];

        yield 'string set with duplicates' => [StringSet::class, ['a', 'a']];

        yield 'number set with non-numeric value' => [NumberSet::class, ['1', 'x']];

        yield 'blob set with non-string value' => [BlobSet::class, [1]];

        yield 'non-empty string list with empty string' => [NonEmptyStringList::class, ['']];

        yield 'string list with non-string value' => [StringList::class, ['a', 1]];

        yield 'non-empty string map with empty string' => [NonEmptyStringMap::class, ['a' => '']];
    }
}
