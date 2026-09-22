<?php

declare(strict_types=1);

namespace Imper86\DynamoDBClientTests\Model;

use InvalidArgumentException;
use Imper86\DynamoDBClient\Model\AttributeValue;
use Imper86\DynamoDBClient\Model\AttributeValueList;
use Imper86\DynamoDBClient\Model\ComparisonOperator;
use Imper86\DynamoDBClient\Model\ExpectedAttributeValue;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(ExpectedAttributeValue::class)]
final class ExpectedAttributeValueTest extends TestCase
{
    /**
     * @throws InvalidArgumentException
     */
    public function testBuildsAComparison(): void
    {
        $expected = ExpectedAttributeValue::comparison(
            ComparisonOperator::IN,
            AttributeValue::string('Available'),
            AttributeValue::string('Backordered'),
        );

        self::assertSame(ComparisonOperator::IN, $expected->comparisonOperator);
        self::assertCount(2, $expected->attributeValueList ?? []);
        self::assertNull($expected->exists);
        self::assertNull($expected->value);
    }

    /**
     * @throws InvalidArgumentException
     */
    public function testBuildsAComparisonWithoutOperands(): void
    {
        $expected = ExpectedAttributeValue::comparison(ComparisonOperator::NOT_NULL);

        self::assertSame(ComparisonOperator::NOT_NULL, $expected->comparisonOperator);
        self::assertNull($expected->attributeValueList);
    }

    /**
     * @throws InvalidArgumentException
     */
    public function testBuildsAnExpectationThatTheAttributeIsAbsent(): void
    {
        $expected = ExpectedAttributeValue::notExists();

        self::assertFalse($expected->exists);
        self::assertNull($expected->value);
    }

    /**
     * @throws InvalidArgumentException
     */
    public function testBuildsAnExpectedValue(): void
    {
        $value = AttributeValue::string('Available');

        $expected = ExpectedAttributeValue::value($value);

        self::assertSame($value, $expected->value);
        self::assertNull($expected->exists);
        self::assertNull($expected->comparisonOperator);
    }

    /**
     * @throws InvalidArgumentException
     */
    public function testAcceptsAValueThatIsExplicitlyExpectedToExist(): void
    {
        $expected = new ExpectedAttributeValue(exists: true, value: AttributeValue::string('Available'));

        self::assertTrue($expected->exists);
    }

    /**
     * @throws InvalidArgumentException
     */
    public function testRejectsAnEmptyExpectation(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessageIsOrContains('Expected a Value, a ComparisonOperator, or Exists set to false.');

        new ExpectedAttributeValue();
    }

    /**
     * @throws InvalidArgumentException
     */
    public function testRejectsAnExistingAttributeWithoutAValue(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessageIsOrContains('Expected a Value, a ComparisonOperator, or Exists set to false.');

        new ExpectedAttributeValue(exists: true);
    }

    /**
     * @throws InvalidArgumentException
     */
    public function testRejectsAValueExpectedNotToExist(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessageIsOrContains('A Value cannot be expected when Exists is false.');

        new ExpectedAttributeValue(exists: false, value: AttributeValue::string('Available'));
    }

    /**
     * @throws InvalidArgumentException
     */
    public function testRejectsAnAttributeValueListWithoutAComparisonOperator(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessageIsOrContains('An AttributeValueList needs a ComparisonOperator.');

        new ExpectedAttributeValue(attributeValueList: new AttributeValueList([AttributeValue::string('Available')]));
    }

    /**
     * @throws InvalidArgumentException
     */
    public function testRejectsAComparisonCombinedWithAValue(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessageIsOrContains(
            'Value and Exists cannot be combined with ComparisonOperator and AttributeValueList.',
        );

        new ExpectedAttributeValue(
            comparisonOperator: ComparisonOperator::NOT_NULL,
            value: AttributeValue::string('Available'),
        );
    }

    /**
     * @throws InvalidArgumentException
     */
    public function testRejectsAComparisonCombinedWithExists(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessageIsOrContains(
            'Value and Exists cannot be combined with ComparisonOperator and AttributeValueList.',
        );

        new ExpectedAttributeValue(comparisonOperator: ComparisonOperator::NULL, exists: false);
    }

    /**
     * @return iterable<string, array{ComparisonOperator, int}>
     */
    public static function provideRejectsAnOperandCountTheOperatorDoesNotTakeCases(): iterable
    {
        yield 'NULL with one value' => [ComparisonOperator::NULL, 1];

        yield 'NOT_NULL with one value' => [ComparisonOperator::NOT_NULL, 1];

        yield 'BETWEEN with one value' => [ComparisonOperator::BETWEEN, 1];

        yield 'BETWEEN with three values' => [ComparisonOperator::BETWEEN, 3];

        yield 'IN without values' => [ComparisonOperator::IN, 0];

        yield 'EQ without values' => [ComparisonOperator::EQ, 0];

        yield 'EQ with two values' => [ComparisonOperator::EQ, 2];

        yield 'BEGINS_WITH with two values' => [ComparisonOperator::BEGINS_WITH, 2];
    }

    /**
     * @throws InvalidArgumentException
     */
    #[DataProvider('provideRejectsAnOperandCountTheOperatorDoesNotTakeCases')]
    public function testRejectsAnOperandCountTheOperatorDoesNotTake(
        ComparisonOperator $comparisonOperator,
        int $operands,
    ): void {
        $values = [];

        for ($i = 0; $i < $operands; ++$i) {
            $values[] = AttributeValue::number($i);
        }

        $this->expectException(InvalidArgumentException::class);

        ExpectedAttributeValue::comparison($comparisonOperator, ...$values);
    }
}
