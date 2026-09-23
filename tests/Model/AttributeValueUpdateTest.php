<?php

declare(strict_types=1);

namespace Imper86\DynamoDBClientTests\Model;

use InvalidArgumentException;
use Imper86\DynamoDBClient\Model\AttributeAction;
use Imper86\DynamoDBClient\Model\AttributeValue;
use Imper86\DynamoDBClient\Model\AttributeValueUpdate;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(AttributeValueUpdate::class)]
final class AttributeValueUpdateTest extends TestCase
{
    /**
     * @throws InvalidArgumentException
     */
    public function testBuildsAnAdd(): void
    {
        $value = AttributeValue::number(1);

        $update = AttributeValueUpdate::add($value);

        self::assertSame(AttributeAction::ADD, $update->action);
        self::assertSame($value, $update->value);
    }

    /**
     * @throws InvalidArgumentException
     */
    public function testBuildsAPut(): void
    {
        $value = AttributeValue::string('alice@example.com');

        $update = AttributeValueUpdate::put($value);

        self::assertSame(AttributeAction::PUT, $update->action);
        self::assertSame($value, $update->value);
    }

    /**
     * @throws InvalidArgumentException
     */
    public function testBuildsADeleteOfTheWholeAttribute(): void
    {
        $update = AttributeValueUpdate::delete();

        self::assertSame(AttributeAction::DELETE, $update->action);
        self::assertNull($update->value);
    }

    /**
     * @throws InvalidArgumentException
     */
    public function testBuildsADeleteOfSetElements(): void
    {
        $value = AttributeValue::stringSet('HelpMe');

        $update = AttributeValueUpdate::delete($value);

        self::assertSame(AttributeAction::DELETE, $update->action);
        self::assertSame($value, $update->value);
    }

    /**
     * @throws InvalidArgumentException
     */
    public function testAcceptsAValueWithoutAnAction(): void
    {
        $update = new AttributeValueUpdate(value: AttributeValue::string('alice@example.com'));

        self::assertNull($update->action);
    }

    /**
     * @throws InvalidArgumentException
     */
    public function testRejectsAnUpdateWithoutAnythingToDo(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessageIsOrContains('Only a DELETE can go without a Value.');

        new AttributeValueUpdate();
    }

    /**
     * @throws InvalidArgumentException
     */
    public function testRejectsAnAddWithoutAValue(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessageIsOrContains('Only a DELETE can go without a Value.');

        new AttributeValueUpdate(action: AttributeAction::ADD);
    }
}
