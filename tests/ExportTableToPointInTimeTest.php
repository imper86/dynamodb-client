<?php

declare(strict_types=1);

namespace Imper86\DynamoDBClientTests;

use DateTimeImmutable;
use Http\Discovery\Exception\NotFoundException;
use Http\Mock\Client as MockClient;
use InvalidArgumentException;
use Nyholm\Psr7\Request;
use Nyholm\Psr7\Response;
use Imper86\DynamoDBClient\DynamoDBClient;
use Imper86\DynamoDBClient\Exception\ExceptionInterface;
use Imper86\DynamoDBClient\Exception\MissingCredentialsException;
use Imper86\DynamoDBClient\Exception\ResponseDeserializationException;
use Imper86\DynamoDBClient\Message\ExportTableToPointInTimeRequest;
use Imper86\DynamoDBClient\Model\Credentials;
use Imper86\DynamoDBClient\Model\ExportDescription;
use Imper86\DynamoDBClient\Model\ExportFormat;
use Imper86\DynamoDBClient\Model\ExportStatus;
use Imper86\DynamoDBClient\Model\ExportType;
use Imper86\DynamoDBClient\Model\ExportViewType;
use Imper86\DynamoDBClient\Model\S3SseAlgorithm;
use JsonException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

use function file_get_contents;
use function json_decode;
use function str_repeat;

use const JSON_THROW_ON_ERROR;

/**
 * The ExportTableToPointInTime reference has no Examples section, so the fixtures are built from its
 * request and response syntax: a full export of a `Music` table to a KMS-encrypted bucket of another
 * account, answered while the export is still in progress.
 *
 * @see https://docs.aws.amazon.com/amazondynamodb/latest/APIReference/API_ExportTableToPointInTime.html
 * @internal
 */
#[CoversClass(DynamoDBClient::class)]
final class ExportTableToPointInTimeTest extends TestCase
{
    private const string REQUEST_FIXTURE = __DIR__ . '/fixtures/export-table-to-point-in-time-request.json';

    private const string RESPONSE_FIXTURE = __DIR__ . '/fixtures/export-table-to-point-in-time-response.json';

    private const string TABLE_ARN = 'arn:aws:dynamodb:eu-central-1:123456789012:table/Music';

    /**
     * @throws ExceptionInterface
     * @throws InvalidArgumentException
     * @throws MissingCredentialsException
     * @throws NotFoundException
     */
    public function testSendsTheRequestTheWayTheApiReferenceDocumentsIt(): void
    {
        $httpClient = new MockClient();
        $httpClient->addResponse($this->documentedResponse());

        $this->createClient($httpClient)->exportTableToPointInTime($this->documentedRequest());

        $sent = $httpClient->getLastRequest();

        self::assertInstanceOf(Request::class, $sent);
        self::assertSame('DynamoDB_20120810.ExportTableToPointInTime', $sent->getHeaderLine('X-Amz-Target'));
        self::assertJsonStringEqualsJsonFile(self::REQUEST_FIXTURE, $sent->getBody()->__toString());
    }

    /**
     * @throws ExceptionInterface
     * @throws InvalidArgumentException
     * @throws MissingCredentialsException
     * @throws NotFoundException
     * @throws JsonException
     */
    public function testSendsThePeriodOfAnIncrementalExport(): void
    {
        $httpClient = new MockClient();
        $httpClient->addResponse($this->documentedResponse());

        $this->createClient($httpClient)->exportTableToPointInTime(ExportTableToPointInTimeRequest::incremental(
            s3Bucket: 'music-exports',
            tableArn: self::TABLE_ARN,
            exportFromTime: new DateTimeImmutable('@1576537666'),
            exportToTime: new DateTimeImmutable('@1576624066'),
            exportViewType: ExportViewType::NEW_AND_OLD_IMAGES,
        ));

        $sent = $httpClient->getLastRequest();

        self::assertInstanceOf(Request::class, $sent);
        self::assertSame(
            [
                'S3Bucket' => 'music-exports',
                'TableArn' => self::TABLE_ARN,
                'ExportType' => 'INCREMENTAL_EXPORT',
                'IncrementalExportSpecification' => [
                    'ExportFromTime' => 1576537666.0,
                    'ExportToTime' => 1576624066.0,
                    'ExportViewType' => 'NEW_AND_OLD_IMAGES',
                ],
            ],
            json_decode($sent->getBody()->__toString(), true, flags: JSON_THROW_ON_ERROR),
        );
    }

    /**
     * @throws ExceptionInterface
     * @throws InvalidArgumentException
     * @throws MissingCredentialsException
     * @throws NotFoundException
     */
    public function testReturnsTheDescriptionOfTheStartedExport(): void
    {
        $httpClient = new MockClient();
        $httpClient->addResponse($this->documentedResponse());

        $export = $this->createClient($httpClient)
            ->exportTableToPointInTime($this->documentedRequest())
            ->exportDescription
        ;

        self::assertInstanceOf(ExportDescription::class, $export);
        self::assertSame(
            'arn:aws:dynamodb:eu-central-1:123456789012:table/Music/export/01576624066799-a1b2c3d4',
            $export->exportArn,
        );
        self::assertSame(ExportStatus::IN_PROGRESS, $export->exportStatus);
        self::assertSame(ExportFormat::DYNAMODB_JSON, $export->exportFormat);
        self::assertSame(ExportType::FULL_EXPORT, $export->exportType);
        self::assertSame('music-exports', $export->s3Bucket);
        self::assertSame(S3SseAlgorithm::KMS, $export->s3SseAlgorithm);
        self::assertSame(self::TABLE_ARN, $export->tableArn);
        self::assertNull($export->endTime);
        self::assertNull($export->exportManifest);

        $exportTime = $export->exportTime;

        self::assertInstanceOf(DateTimeImmutable::class, $exportTime);
        self::assertSame('2019-12-17T23:07:46.799000+00:00', $exportTime->format('Y-m-d\TH:i:s.uP'));
    }

    /**
     * @throws ExceptionInterface
     * @throws InvalidArgumentException
     * @throws MissingCredentialsException
     * @throws NotFoundException
     */
    public function testLeavesTheExportDescriptionNullWhenTheServiceOmitsIt(): void
    {
        $httpClient = new MockClient();
        $httpClient->addResponse(new Response(body: '{}'));

        $response = $this->createClient($httpClient)->exportTableToPointInTime($this->documentedRequest());

        self::assertNull($response->exportDescription);
    }

    /**
     * @throws ExceptionInterface
     * @throws InvalidArgumentException
     * @throws MissingCredentialsException
     * @throws NotFoundException
     */
    public function testFailsOnABodyItCannotDeserialize(): void
    {
        $httpClient = new MockClient();
        $httpClient->addResponse(new Response(body: '{"ExportDescription":{"ExportStatus":"PENDING"}}'));

        $this->expectException(ResponseDeserializationException::class);

        $this->createClient($httpClient)->exportTableToPointInTime($this->documentedRequest());
    }

    /**
     * @throws InvalidArgumentException
     */
    public function testRejectsABucketNameLongerThanTheServiceAllows(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new ExportTableToPointInTimeRequest(str_repeat('a', 256), self::TABLE_ARN);
    }

    /**
     * @throws InvalidArgumentException
     */
    public function testRejectsABucketNameThatDoesNotEndInALetterOrDigit(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new ExportTableToPointInTimeRequest('music-exports-', self::TABLE_ARN);
    }

    /**
     * @throws InvalidArgumentException
     */
    public function testRejectsATableArnLongerThanTheServiceAllows(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new ExportTableToPointInTimeRequest('music-exports', str_repeat('a', 1025));
    }

    /**
     * @throws InvalidArgumentException
     */
    public function testRejectsAClientTokenWithADollarSign(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new ExportTableToPointInTimeRequest('music-exports', self::TABLE_ARN, clientToken: 'music$export');
    }

    /**
     * @throws InvalidArgumentException
     */
    public function testRejectsABucketOwnerThatIsNotATwelveDigitAccountId(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new ExportTableToPointInTimeRequest('music-exports', self::TABLE_ARN, s3BucketOwner: '12345678901');
    }

    /**
     * @throws InvalidArgumentException
     */
    public function testRejectsAPrefixLongerThanTheServiceAllows(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new ExportTableToPointInTimeRequest('music-exports', self::TABLE_ARN, s3Prefix: str_repeat('a', 1025));
    }

    /**
     * @throws InvalidArgumentException
     */
    public function testRejectsAKmsKeyIdLongerThanTheServiceAllows(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new ExportTableToPointInTimeRequest('music-exports', self::TABLE_ARN, s3SseKmsKeyId: str_repeat('a', 2049));
    }

    /**
     * @throws InvalidArgumentException
     */
    public function testRejectsAnIncrementalExportWithoutItsPeriod(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new ExportTableToPointInTimeRequest(
            'music-exports',
            self::TABLE_ARN,
            exportType: ExportType::INCREMENTAL_EXPORT,
        );
    }

    /**
     * @throws InvalidArgumentException
     */
    private function documentedRequest(): ExportTableToPointInTimeRequest
    {
        return ExportTableToPointInTimeRequest::full(
            s3Bucket: 'music-exports',
            tableArn: self::TABLE_ARN,
            clientToken: '8f3b8f4e-0a6c-4b53-9d8e-2c1f6a7b9e10',
            exportFormat: ExportFormat::DYNAMODB_JSON,
            exportTime: new DateTimeImmutable('@1576624066.799'),
            s3BucketOwner: '123456789012',
            s3Prefix: 'exports/music',
            s3SseAlgorithm: S3SseAlgorithm::KMS,
            s3SseKmsKeyId: 'arn:aws:kms:eu-central-1:123456789012:key/1234abcd-12ab-34cd-56ef-1234567890ab',
        );
    }

    /**
     * @throws InvalidArgumentException
     * @throws MissingCredentialsException
     * @throws NotFoundException
     */
    private function createClient(MockClient $httpClient): DynamoDBClient
    {
        return new DynamoDBClient(
            'eu-central-1',
            new Credentials('AKIDEXAMPLE', 'secret'),
            $httpClient,
        );
    }

    private function documentedResponse(): Response
    {
        $body = file_get_contents(self::RESPONSE_FIXTURE);

        self::assertIsString($body);

        return new Response(body: $body);
    }
}
