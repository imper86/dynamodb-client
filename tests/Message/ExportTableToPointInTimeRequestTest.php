<?php

declare(strict_types=1);

namespace Imper86\DynamoDBClientTests\Message;

use DateTimeImmutable;
use Imper86\DynamoDBClient\Message\ExportTableToPointInTimeRequest;
use Imper86\DynamoDBClient\Model\ExportType;
use Imper86\DynamoDBClient\Model\IncrementalExportSpecification;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(ExportTableToPointInTimeRequest::class)]
final class ExportTableToPointInTimeRequestTest extends TestCase
{
    private const TABLE_ARN = 'arn:aws:dynamodb:eu-central-1:123456789012:table/Music';

    /**
     * @throws InvalidArgumentException
     */
    public function testBuildsAFullExport(): void
    {
        $exportTime = new DateTimeImmutable('@1576624066');

        $request = ExportTableToPointInTimeRequest::full('music-exports', self::TABLE_ARN, exportTime: $exportTime);

        self::assertSame(ExportType::FULL_EXPORT, $request->exportType);
        self::assertSame($exportTime, $request->exportTime);
        self::assertNull($request->incrementalExportSpecification);
    }

    /**
     * Without a period the specification is still sent, as `{}`, and the service picks the defaults.
     *
     * @throws InvalidArgumentException
     */
    public function testBuildsAnIncrementalExportEvenWithoutAPeriod(): void
    {
        $request = ExportTableToPointInTimeRequest::incremental('music-exports', self::TABLE_ARN);

        self::assertSame(ExportType::INCREMENTAL_EXPORT, $request->exportType);
        self::assertInstanceOf(IncrementalExportSpecification::class, $request->incrementalExportSpecification);
        self::assertNull($request->exportTime);
    }

    /**
     * @throws InvalidArgumentException
     */
    public function testValidatesThroughTheConstructor(): void
    {
        $this->expectException(InvalidArgumentException::class);

        ExportTableToPointInTimeRequest::incremental('music exports', self::TABLE_ARN);
    }
}
