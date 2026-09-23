<?php

declare(strict_types=1);

namespace Imper86\DynamoDBClientTests\Message;

use Imper86\DynamoDBClient\Message\UpdateKinesisStreamingDestinationRequest;
use Imper86\DynamoDBClient\Model\ApproximateCreationDateTimePrecision;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(UpdateKinesisStreamingDestinationRequest::class)]
final class UpdateKinesisStreamingDestinationRequestTest extends TestCase
{
    private const string STREAM_ARN = 'arn:aws:kinesis:us-west-2:123456789012:stream/MusicStream';

    /**
     * @throws InvalidArgumentException
     */
    public function testChangesThePrecision(): void
    {
        $request = UpdateKinesisStreamingDestinationRequest::precision(
            self::STREAM_ARN,
            'Music',
            ApproximateCreationDateTimePrecision::MILLISECOND,
        );

        self::assertSame(self::STREAM_ARN, $request->streamArn);
        self::assertSame('Music', $request->tableName);
        self::assertSame(
            ApproximateCreationDateTimePrecision::MILLISECOND,
            $request->updateKinesisStreamingConfiguration?->approximateCreationDateTimePrecision,
        );
    }

    /**
     * @throws InvalidArgumentException
     */
    public function testValidatesThroughTheConstructor(): void
    {
        $this->expectException(InvalidArgumentException::class);

        UpdateKinesisStreamingDestinationRequest::precision(
            'arn:too-short',
            'Music',
            ApproximateCreationDateTimePrecision::MILLISECOND,
        );
    }
}
