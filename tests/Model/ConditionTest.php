<?php

declare(strict_types=1);

namespace Imper86\DynamoDBClientTests\Model;

use InvalidArgumentException;
use Imper86\DynamoDBClient\Model\AttributeValue;
use Imper86\DynamoDBClient\Model\ComparisonOperator;
use Imper86\DynamoDBClient\Model\Condition;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(Condition::class)]
final class ConditionTest extends TestCase
{
    /**
     * @throws InvalidArgumentException
     */
    public function testBuildsAComparison(): void
    {
        $condition = Condition::comparison(
            ComparisonOperator::BETWEEN,
            AttributeValue::string('User A'),
            AttributeValue::string('User C'),
        );

        self::assertSame(ComparisonOperator::BETWEEN, $condition->comparisonOperator);
        self::assertCount(2, $condition->attributeValueList ?? []);
    }

    /**
     * @throws InvalidArgumentException
     */
    public function testBuildsAComparisonWithoutOperands(): void
    {
        $condition = Condition::comparison(ComparisonOperator::NOT_NULL);

        self::assertSame(ComparisonOperator::NOT_NULL, $condition->comparisonOperator);
        self::assertNull($condition->attributeValueList);
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

        Condition::comparison($comparisonOperator, ...$values);
    }
}
