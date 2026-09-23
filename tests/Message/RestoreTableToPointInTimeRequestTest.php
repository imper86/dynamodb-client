<?php

declare(strict_types=1);

namespace Imper86\DynamoDBClientTests\Message;

use DateTimeImmutable;
use Imper86\DynamoDBClient\Message\RestoreTableToPointInTimeRequest;
use Imper86\DynamoDBClient\Model\BillingMode;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(RestoreTableToPointInTimeRequest::class)]
final class RestoreTableToPointInTimeRequestTest extends TestCase
{
    private const string SOURCE_TABLE_ARN = 'arn:aws:dynamodb:eu-central-1:123456789012:table/Music';

    /**
     * @throws InvalidArgumentException
     */
    public function testRestoresATableNamedByNameToAPointInTime(): void
    {
        $restoreDateTime = new DateTimeImmutable('@1576624066.799');

        $request = RestoreTableToPointInTimeRequest::at('Music', 'MusicRestored', $restoreDateTime);

        self::assertSame('Music', $request->sourceTableName);
        self::assertNull($request->sourceTableArn);
        self::assertSame('MusicRestored', $request->targetTableName);
        self::assertSame($restoreDateTime, $request->restoreDateTime);
        self::assertNull($request->useLatestRestorableTime);
    }

    /**
     * @throws InvalidArgumentException
     */
    public function testRestoresATableNamedByArnToThePointItWasLastRestorable(): void
    {
        $request = RestoreTableToPointInTimeRequest::latest(self::SOURCE_TABLE_ARN, 'MusicRestored');

        self::assertSame(self::SOURCE_TABLE_ARN, $request->sourceTableArn);
        self::assertNull($request->sourceTableName);
        self::assertSame('MusicRestored', $request->targetTableName);
        self::assertTrue($request->useLatestRestorableTime);
        self::assertNull($request->restoreDateTime);
    }

    /**
     * @throws InvalidArgumentException
     */
    public function testForwardsTheOverrides(): void
    {
        $request = RestoreTableToPointInTimeRequest::latest(
            'Music',
            'MusicRestored',
            billingModeOverride: BillingMode::PAY_PER_REQUEST,
        );

        self::assertSame(BillingMode::PAY_PER_REQUEST, $request->billingModeOverride);
    }

    /**
     * @throws InvalidArgumentException
     */
    public function testValidatesThroughTheConstructor(): void
    {
        $this->expectException(InvalidArgumentException::class);

        RestoreTableToPointInTimeRequest::latest('Music Table', 'MusicRestored');
    }
}
