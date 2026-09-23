<?php

declare(strict_types=1);

namespace Imper86\DynamoDBClientTests\Message;

use Imper86\DynamoDBClient\Message\UpdateContinuousBackupsRequest;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(UpdateContinuousBackupsRequest::class)]
final class UpdateContinuousBackupsRequestTest extends TestCase
{
    /**
     * @throws InvalidArgumentException
     */
    public function testEnablesWithTheServiceDefaultPeriod(): void
    {
        $request = UpdateContinuousBackupsRequest::enable('Music');

        self::assertSame('Music', $request->tableName);
        self::assertTrue($request->pointInTimeRecoverySpecification->pointInTimeRecoveryEnabled);
        self::assertNull($request->pointInTimeRecoverySpecification->recoveryPeriodInDays);
    }

    /**
     * @throws InvalidArgumentException
     */
    public function testEnablesWithAGivenPeriod(): void
    {
        $request = UpdateContinuousBackupsRequest::enable('Music', 14);

        self::assertSame(14, $request->pointInTimeRecoverySpecification->recoveryPeriodInDays);
    }

    /**
     * @throws InvalidArgumentException
     */
    public function testDisables(): void
    {
        $request = UpdateContinuousBackupsRequest::disable('Music');

        self::assertSame('Music', $request->tableName);
        self::assertFalse($request->pointInTimeRecoverySpecification->pointInTimeRecoveryEnabled);
        self::assertNull($request->pointInTimeRecoverySpecification->recoveryPeriodInDays);
    }

    /**
     * @throws InvalidArgumentException
     */
    public function testValidatesThroughTheConstructor(): void
    {
        $this->expectException(InvalidArgumentException::class);

        UpdateContinuousBackupsRequest::enable('Music', 36);
    }
}
