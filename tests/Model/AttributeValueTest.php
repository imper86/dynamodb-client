<?php

declare(strict_types=1);

namespace Imper86\DynamoDBClientTests\Model;

use InvalidArgumentException;
use Imper86\DynamoDBClient\Model\AttributeValue;
use Imper86\DynamoDBClient\Model\AttributeValueList;
use Imper86\DynamoDBClient\Model\AttributeValueMap;
use Imper86\DynamoDBClient\ValueObject\BlobSet;
use Imper86\DynamoDBClient\ValueObject\NumberSet;
use Imper86\DynamoDBClient\ValueObject\StringSet;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

use function base64_encode;

/**
 * @internal
 */
#[CoversClass(AttributeValue::class)]
final class AttributeValueTest extends TestCase
{
    /**
     * @throws InvalidArgumentException
     */
    public function testBuildsAScalarValue(): void
    {
        $blob = base64_encode('dGhpcyB0ZXh0IGlzIGJhc2U2NC1lbmNvZGVk');

        self::assertSame($blob, AttributeValue::blob($blob)->blob);
        self::assertTrue(AttributeValue::bool(true)->bool);
        self::assertSame('Acme Band', AttributeValue::string('Acme Band')->string);
        self::assertTrue(AttributeValue::null()->null);
    }

    /**
     * @throws InvalidArgumentException
     */
    public function testSetsNothingButTheValueItWasAskedFor(): void
    {
        $value = AttributeValue::string('Acme Band');

        self::assertNull($value->blob);
        self::assertNull($value->bool);
        self::assertNull($value->blobSet);
        self::assertNull($value->list);
        self::assertNull($value->map);
        self::assertNull($value->number);
        self::assertNull($value->numberSet);
        self::assertNull($value->null);
        self::assertNull($value->stringSet);
    }

    /**
     * @throws InvalidArgumentException
     */
    public function testConvertsANumberToTheStringTheWireExpects(): void
    {
        self::assertSame('5', AttributeValue::number(5)->number);
        self::assertSame('1.5', AttributeValue::number(1.5)->number);
        self::assertSame('0.00000000001', AttributeValue::number('0.00000000001')->number);
    }

    /**
     * @throws InvalidArgumentException
     */
    public function testRejectsANumberThatIsNotNumeric(): void
    {
        $this->expectException(InvalidArgumentException::class);

        AttributeValue::number('one');
    }

    /**
     * @throws InvalidArgumentException
     */
    public function testBuildsASet(): void
    {
        $blobSet = AttributeValue::blobSet('c3VycmV5', 'bGFrZQ==')->blobSet;

        self::assertInstanceOf(BlobSet::class, $blobSet);
        self::assertSame(['c3VycmV5', 'bGFrZQ=='], $blobSet->toArray());

        $numberSet = AttributeValue::numberSet(42, 1.5, '3')->numberSet;

        self::assertInstanceOf(NumberSet::class, $numberSet);
        self::assertSame(['42', '1.5', '3'], $numberSet->toArray());

        $stringSet = AttributeValue::stringSet('Giraffe', 'Hippo')->stringSet;

        self::assertInstanceOf(StringSet::class, $stringSet);
        self::assertSame(['Giraffe', 'Hippo'], $stringSet->toArray());
    }

    /**
     * @throws InvalidArgumentException
     */
    public function testRejectsASetWithADuplicate(): void
    {
        $this->expectException(InvalidArgumentException::class);

        AttributeValue::numberSet(1, '1');
    }

    /**
     * @throws InvalidArgumentException
     */
    public function testBuildsAList(): void
    {
        $list = AttributeValue::list(AttributeValue::string('Cookies'), AttributeValue::number(3))->list;

        self::assertInstanceOf(AttributeValueList::class, $list);
        self::assertCount(2, $list);
        self::assertSame('Cookies', $list->get(0)?->string);
        self::assertSame('3', $list->get(1)?->number);
    }

    /**
     * @throws InvalidArgumentException
     */
    public function testBuildsAMap(): void
    {
        $map = AttributeValue::map([
            'Name' => AttributeValue::string('Amazon DynamoDB'),
            'Tags' => AttributeValue::stringSet('Multimedia', 'Entertainment'),
        ])->map;

        self::assertInstanceOf(AttributeValueMap::class, $map);
        self::assertSame(['Name', 'Tags'], $map->keys());
        self::assertSame('Amazon DynamoDB', $map->get('Name')?->string);
        self::assertSame(['Multimedia', 'Entertainment'], $map->get('Tags')?->stringSet?->toArray());
    }
}
