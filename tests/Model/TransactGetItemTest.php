<?php

declare(strict_types=1);

namespace Imper86\DynamoDBClientTests\Model;

use InvalidArgumentException;
use Imper86\DynamoDBClient\Model\AttributeValue;
use Imper86\DynamoDBClient\Model\AttributeValueMap;
use Imper86\DynamoDBClient\Model\TransactGetItem;
use Imper86\DynamoDBClient\ValueObject\NonEmptyStringMap;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

use function str_repeat;

/**
 * @internal
 */
#[CoversClass(TransactGetItem::class)]
final class TransactGetItemTest extends TestCase
{
    /**
     * @throws InvalidArgumentException
     */
    public function testBuildsAGet(): void
    {
        $key = new AttributeValueMap(['Artist' => AttributeValue::string('Acme Band')]);
        $names = new NonEmptyStringMap(['#title' => 'SongTitle']);

        $transactGetItem = TransactGetItem::get($key, 'Music', $names, '#title');

        self::assertSame($key, $transactGetItem->get->key);
        self::assertSame('Music', $transactGetItem->get->tableName);
        self::assertSame($names, $transactGetItem->get->expressionAttributeNames);
        self::assertSame('#title', $transactGetItem->get->projectionExpression);
    }

    /**
     * @throws InvalidArgumentException
     */
    public function testValidatesThroughTheGetConstructor(): void
    {
        $key = new AttributeValueMap(['Artist' => AttributeValue::string('Acme Band')]);

        $this->expectException(InvalidArgumentException::class);

        TransactGetItem::get($key, str_repeat('a', 1025));
    }
}
