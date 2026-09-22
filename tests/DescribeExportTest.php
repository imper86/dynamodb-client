<?php

declare(strict_types=1);

namespace Imper86\DynamoDBClientTests;

use DateTimeImmutable;
use Http\Discovery\Exception\NotFoundException;
use Http\Mock\Client as MockClient;
use Imper86\DynamoDBClient\DynamoDBClient;
use Imper86\DynamoDBClient\Exception\ExceptionInterface;
use Imper86\DynamoDBClient\Exception\MissingCredentialsException;
use Imper86\DynamoDBClient\Exception\ResponseDeserializationException;
use Imper86\DynamoDBClient\Message\DescribeExportRequest;
use Imper86\DynamoDBClient\Model\Credentials;
use Imper86\DynamoDBClient\Model\ExportDescription;
use Imper86\DynamoDBClient\Model\ExportFormat;
use Imper86\DynamoDBClient\Model\ExportStatus;
use Imper86\DynamoDBClient\Model\ExportType;
use Imper86\DynamoDBClient\Model\ExportViewType;
use Imper86\DynamoDBClient\Model\IncrementalExportSpecification;
use Imper86\DynamoDBClient\Model\S3SseAlgorithm;
use InvalidArgumentException;
use Nyholm\Psr7\Request;
use Nyholm\Psr7\Response;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

use function file_get_contents;
use function str_repeat;

/**
 * The DescribeExport reference has no Examples section, so the fixtures are built from its request and
 * response syntax, describing a completed, KMS-encrypted full export of the `Music` table. The incremental
 * and failed variants are inlined in the tests that need them.
 *
 * @see https://docs.aws.amazon.com/amazondynamodb/latest/APIReference/API_DescribeExport.html
 * @internal
 */
#[CoversClass(DynamoDBClient::class)]
final class DescribeExportTest extends TestCase
{
    private const string EXPORT_ARN =
        'arn:aws:dynamodb:eu-central-1:123456789012:table/Music/export/01576624066799-a1b2c3d4';

    private const string REQUEST_FIXTURE = __DIR__ . '/fixtures/describe-export-request.json';

    private const string RESPONSE_FIXTURE = __DIR__ . '/fixtures/describe-export-response.json';

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

        $this->createClient($httpClient)->describeExport(new DescribeExportRequest(self::EXPORT_ARN));

        $sent = $httpClient->getLastRequest();

        self::assertInstanceOf(Request::class, $sent);
        self::assertSame('DynamoDB_20120810.DescribeExport', $sent->getHeaderLine('X-Amz-Target'));
        self::assertJsonStringEqualsJsonFile(self::REQUEST_FIXTURE, $sent->getBody()->__toString());
    }

    /**
     * @throws ExceptionInterface
     * @throws InvalidArgumentException
     * @throws MissingCredentialsException
     * @throws NotFoundException
     */
    public function testReturnsTheDetailsOfTheExport(): void
    {
        $httpClient = new MockClient();
        $httpClient->addResponse($this->documentedResponse());

        $response = $this->createClient($httpClient)->describeExport(new DescribeExportRequest(self::EXPORT_ARN));

        $export = $response->exportDescription;

        self::assertInstanceOf(ExportDescription::class, $export);
        self::assertSame(123456, $export->billedSizeBytes);
        self::assertSame('8f3b8f4e-0a6c-4b53-9d8e-2c1f6a7b9e10', $export->clientToken);
        self::assertSame(self::EXPORT_ARN, $export->exportArn);
        self::assertSame(ExportFormat::DYNAMODB_JSON, $export->exportFormat);
        self::assertSame('AWSDynamoDB/01576624066799-a1b2c3d4/manifest-summary.json', $export->exportManifest);
        self::assertSame(ExportStatus::COMPLETED, $export->exportStatus);
        self::assertSame(ExportType::FULL_EXPORT, $export->exportType);
        self::assertNull($export->failureCode);
        self::assertNull($export->failureMessage);
        self::assertNull($export->incrementalExportSpecification);
        self::assertSame(42, $export->itemCount);
        self::assertSame('music-exports', $export->s3Bucket);
        self::assertSame('123456789012', $export->s3BucketOwner);
        self::assertSame('exports/music', $export->s3Prefix);
        self::assertSame(S3SseAlgorithm::KMS, $export->s3SseAlgorithm);
        self::assertSame(
            'arn:aws:kms:eu-central-1:123456789012:key/1234abcd-12ab-34cd-56ef-1234567890ab',
            $export->s3SseKmsKeyId,
        );
        self::assertSame('arn:aws:dynamodb:eu-central-1:123456789012:table/Music', $export->tableArn);
        self::assertSame('e0a1b2c3-d4e5-4f60-8a7b-9c8d7e6f5a4b', $export->tableId);
    }

    /**
     * @throws ExceptionInterface
     * @throws InvalidArgumentException
     * @throws MissingCredentialsException
     * @throws NotFoundException
     */
    public function testReturnsTheTimesOfTheExport(): void
    {
        $httpClient = new MockClient();
        $httpClient->addResponse($this->documentedResponse());

        $export = $this->createClient($httpClient)
            ->describeExport(new DescribeExportRequest(self::EXPORT_ARN))
            ->exportDescription
        ;

        self::assertInstanceOf(ExportDescription::class, $export);

        $exportTime = $export->exportTime;
        $startTime = $export->startTime;
        $endTime = $export->endTime;

        self::assertInstanceOf(DateTimeImmutable::class, $exportTime);
        self::assertInstanceOf(DateTimeImmutable::class, $startTime);
        self::assertInstanceOf(DateTimeImmutable::class, $endTime);
        self::assertSame('2019-12-17T23:07:46.799000+00:00', $exportTime->format('Y-m-d\TH:i:s.uP'));
        self::assertSame('2019-12-17T23:07:50.250000+00:00', $startTime->format('Y-m-d\TH:i:s.uP'));
        self::assertSame('2019-12-17T23:12:46.500000+00:00', $endTime->format('Y-m-d\TH:i:s.uP'));
    }

    /**
     * @throws ExceptionInterface
     * @throws InvalidArgumentException
     * @throws MissingCredentialsException
     * @throws NotFoundException
     */
    public function testReturnsThePeriodOfAnIncrementalExport(): void
    {
        $httpClient = new MockClient();
        $httpClient->addResponse(new Response(body: '{"ExportDescription":{"ExportType":"INCREMENTAL_EXPORT",'
            . '"IncrementalExportSpecification":{"ExportFromTime":1576537666,"ExportToTime":1576624066.799,'
            . '"ExportViewType":"NEW_IMAGE"}}}'));

        $export = $this->createClient($httpClient)
            ->describeExport(new DescribeExportRequest(self::EXPORT_ARN))
            ->exportDescription
        ;

        self::assertInstanceOf(ExportDescription::class, $export);
        self::assertSame(ExportType::INCREMENTAL_EXPORT, $export->exportType);

        $specification = $export->incrementalExportSpecification;

        self::assertInstanceOf(IncrementalExportSpecification::class, $specification);
        self::assertSame(ExportViewType::NEW_IMAGE, $specification->exportViewType);

        $from = $specification->exportFromTime;
        $to = $specification->exportToTime;

        self::assertInstanceOf(DateTimeImmutable::class, $from);
        self::assertInstanceOf(DateTimeImmutable::class, $to);
        self::assertSame('2019-12-16T23:07:46.000000+00:00', $from->format('Y-m-d\TH:i:s.uP'));
        self::assertSame('2019-12-17T23:07:46.799000+00:00', $to->format('Y-m-d\TH:i:s.uP'));
    }

    /**
     * @throws ExceptionInterface
     * @throws InvalidArgumentException
     * @throws MissingCredentialsException
     * @throws NotFoundException
     */
    public function testLeavesThePeriodMembersNullWhenTheServiceOmitsThem(): void
    {
        $httpClient = new MockClient();
        $httpClient->addResponse(new Response(body: '{"ExportDescription":{"IncrementalExportSpecification":{}}}'));

        $specification = $this->createClient($httpClient)
            ->describeExport(new DescribeExportRequest(self::EXPORT_ARN))
            ->exportDescription
            ?->incrementalExportSpecification
        ;

        self::assertInstanceOf(IncrementalExportSpecification::class, $specification);
        self::assertNull($specification->exportFromTime);
        self::assertNull($specification->exportToTime);
        self::assertNull($specification->exportViewType);
    }

    /**
     * A failed export reports why, and has neither an end time nor a manifest.
     *
     * @throws ExceptionInterface
     * @throws InvalidArgumentException
     * @throws MissingCredentialsException
     * @throws NotFoundException
     */
    public function testReturnsWhyAnExportFailed(): void
    {
        $httpClient = new MockClient();
        $httpClient->addResponse(new Response(body: '{"ExportDescription":{"ExportStatus":"FAILED",'
            . '"FailureCode":"S3NoSuchBucket","FailureMessage":"The specified bucket does not exist"}}'));

        $export = $this->createClient($httpClient)
            ->describeExport(new DescribeExportRequest(self::EXPORT_ARN))
            ->exportDescription
        ;

        self::assertInstanceOf(ExportDescription::class, $export);
        self::assertSame(ExportStatus::FAILED, $export->exportStatus);
        self::assertSame('S3NoSuchBucket', $export->failureCode);
        self::assertSame('The specified bucket does not exist', $export->failureMessage);
        self::assertNull($export->endTime);
        self::assertNull($export->exportManifest);
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

        $response = $this->createClient($httpClient)->describeExport(new DescribeExportRequest(self::EXPORT_ARN));

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
        $httpClient->addResponse(new Response(body: '{"ExportDescription":{"ExportStatus":"CANCELLED"}}'));

        $this->expectException(ResponseDeserializationException::class);

        $this->createClient($httpClient)->describeExport(new DescribeExportRequest(self::EXPORT_ARN));
    }

    /**
     * @throws InvalidArgumentException
     */
    public function testRejectsAnExportArnShorterThanThirtySevenCharacters(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new DescribeExportRequest(str_repeat('a', 36));
    }

    /**
     * @throws InvalidArgumentException
     */
    public function testRejectsAnExportArnLongerThanTheServiceAllows(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new DescribeExportRequest(str_repeat('a', 1025));
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
