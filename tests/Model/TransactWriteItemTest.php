<?php

declare(strict_types=1);

namespace Imper86\DynamoDBClientTests\Model;

use InvalidArgumentException;
use Imper86\DynamoDBClient\Model\AttributeValue;
use Imper86\DynamoDBClient\Model\AttributeValueMap;
use Imper86\DynamoDBClient\Model\ReturnValuesOnConditionCheckFailure;
use Imper86\DynamoDBClient\Model\TransactWriteItem;
use Imper86\DynamoDBClient\ValueObject\NonEmptyStringMap;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

use function str_repeat;

/**
 * @internal
 */
#[CoversClass(TransactWriteItem::class)]
final class TransactWriteItemTest extends TestCase
{
    /**
     * @throws InvalidArgumentException
     */
    public function testBuildsAConditionCheck(): void
    {
        $key = $this->key();
        $values = new AttributeValueMap([':min' => AttributeValue::number(1)]);

        $transactWriteItem = TransactWriteItem::conditionCheck(
            'Plays >= :min',
            $key,
            'Music',
            expressionAttributeValues: $values,
            returnValuesOnConditionCheckFailure: ReturnValuesOnConditionCheckFailure::ALL_OLD,
        );

        $conditionCheck = $transactWriteItem->conditionCheck;

        self::assertNull($transactWriteItem->delete);
        self::assertNull($transactWriteItem->put);
        self::assertNull($transactWriteItem->update);
        self::assertSame('Plays >= :min', $conditionCheck?->conditionExpression);
        self::assertSame($key, $conditionCheck->key);
        self::assertSame('Music', $conditionCheck->tableName);
        self::assertSame($values, $conditionCheck->expressionAttributeValues);
        self::assertSame(
            ReturnValuesOnConditionCheckFailure::ALL_OLD,
            $conditionCheck->returnValuesOnConditionCheckFailure,
        );
    }

    /**
     * @throws InvalidArgumentException
     */
    public function testBuildsADelete(): void
    {
        $key = $this->key();

        $transactWriteItem = TransactWriteItem::delete($key, 'Music', 'attribute_exists(Artist)');

        $delete = $transactWriteItem->delete;

        self::assertNull($transactWriteItem->conditionCheck);
        self::assertSame($key, $delete?->key);
        self::assertSame('Music', $delete->tableName);
        self::assertSame('attribute_exists(Artist)', $delete->conditionExpression);
    }

    /**
     * @throws InvalidArgumentException
     */
    public function testBuildsAPut(): void
    {
        $item = $this->key();
        $names = new NonEmptyStringMap(['#title' => 'SongTitle']);

        $transactWriteItem = TransactWriteItem::put(
            $item,
            'Music',
            'attribute_not_exists(#title)',
            expressionAttributeNames: $names,
        );

        $put = $transactWriteItem->put;

        self::assertNull($transactWriteItem->update);
        self::assertSame($item, $put?->item);
        self::assertSame('attribute_not_exists(#title)', $put->conditionExpression);
        self::assertSame($names, $put->expressionAttributeNames);
    }

    /**
     * @throws InvalidArgumentException
     */
    public function testBuildsAnUpdate(): void
    {
        $key = $this->key();

        $transactWriteItem = TransactWriteItem::update($key, 'Music', 'REMOVE Plays');

        $update = $transactWriteItem->update;

        self::assertNull($transactWriteItem->put);
        self::assertSame($key, $update?->key);
        self::assertSame('REMOVE Plays', $update->updateExpression);
        self::assertNull($update->conditionExpression);
    }

    /**
     * @throws InvalidArgumentException
     */
    public function testValidatesThroughTheActionConstructor(): void
    {
        $this->expectException(InvalidArgumentException::class);

        TransactWriteItem::update($this->key(), str_repeat('a', 1025), 'REMOVE Plays');
    }

    /**
     * @throws InvalidArgumentException
     */
    public function testRejectsATransactWriteItemWithoutAnyAction(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessageMatches('/A TransactWriteItem needs exactly one of ConditionCheck, Delete, Put or Update\./');

        new TransactWriteItem();
    }

    /**
     * @throws InvalidArgumentException
     */
    public function testRejectsATransactWriteItemWithTwoActions(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessageMatches('/A TransactWriteItem needs exactly one of ConditionCheck, Delete, Put or Update\./');

        new TransactWriteItem(
            delete: TransactWriteItem::delete($this->key(), 'Music')->delete,
            put: TransactWriteItem::put($this->key(), 'Music')->put,
        );
    }

    /**
     * @throws InvalidArgumentException
     */
    private function key(): AttributeValueMap
    {
        return new AttributeValueMap([
            'Artist' => AttributeValue::string('Acme Band'),
            'SongTitle' => AttributeValue::string('Happy Day'),
        ]);
    }
}
