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
use Imper86\DynamoDBClient\Message\ListImportsRequest;
use Imper86\DynamoDBClient\Model\Credentials;
use Imper86\DynamoDBClient\Model\ImportStatus;
use Imper86\DynamoDBClient\Model\ImportSummary;
use Imper86\DynamoDBClient\Model\InputFormat;
use Imper86\DynamoDBClient\Model\S3BucketSource;
use InvalidArgumentException;
use Nyholm\Psr7\Request;
use Nyholm\Psr7\Response;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

use function file_get_contents;
use function str_repeat;

/**
 * The ListImports reference has no Examples section, so the fixtures are built from its request and
 * response syntax: a page of the imports into a `Music` table, the completed CSV import DescribeImportTest
 * describes and a DynamoDB JSON import still in progress.
 *
 * @see https://docs.aws.amazon.com/amazondynamodb/latest/APIReference/API_ListImports.html
 * @internal
 */
#[CoversClass(DynamoDBClient::class)]
final class ListImportsTest extends TestCase
{
    private const REQUEST_FIXTURE = __DIR__ . '/fixtures/list-imports-request.json';

    private const RESPONSE_FIXTURE = __DIR__ . '/fixtures/list-imports-response.json';

    private const TABLE_ARN = 'arn:aws:dynamodb:eu-central-1:123456789012:table/Music';

    /**
     * Seven blocks of sixteen hexadecimal digits, the shortest token the pattern allows.
     */
    private const NEXT_TOKEN = '0123456789abcdef0123456789abcdf00123456789abcdf10123456789abcdf2'
        . '0123456789abcdf30123456789abcdf40123456789abcdf5';

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

        $this->createClient($httpClient)->listImports(new ListImportsRequest(
            nextToken: self::NEXT_TOKEN,
            pageSize: 2,
            tableArn: self::TABLE_ARN,
        ));

        $sent = $httpClient->getLastRequest();

        self::assertInstanceOf(Request::class, $sent);
        self::assertSame('DynamoDB_20120810.ListImports', $sent->getHeaderLine('X-Amz-Target'));
        self::assertJsonStringEqualsJsonFile(self::REQUEST_FIXTURE, $sent->getBody()->__toString());
    }

    /**
     * @throws ExceptionInterface
     * @throws InvalidArgumentException
     * @throws MissingCredentialsException
     * @throws NotFoundException
     */
    public function testSendsAnEmptyObjectWithoutARequest(): void
    {
        $httpClient = new MockClient();
        $httpClient->addResponse($this->documentedResponse());

        $this->createClient($httpClient)->listImports();

        $sent = $httpClient->getLastRequest();

        self::assertInstanceOf(Request::class, $sent);
        self::assertSame('{}', $sent->getBody()->__toString());
    }

    /**
     * @throws ExceptionInterface
     * @throws InvalidArgumentException
     * @throws MissingCredentialsException
     * @throws NotFoundException
     */
    public function testReturnsTheSummaryOfACompletedImport(): void
    {
        $httpClient = new MockClient();
        $httpClient->addResponse($this->documentedResponse());

        $summaries = $this->createClient($httpClient)->listImports()->importSummaryList;

        self::assertCount(2, $summaries);

        $import = $summaries->get(0);

        self::assertInstanceOf(ImportSummary::class, $import);
        self::assertSame(
            'arn:aws:logs:eu-central-1:123456789012:log-group:/aws-dynamodb/imports:*',
            $import->cloudWatchLogGroupArn,
        );
        self::assertSame(
            'arn:aws:dynamodb:eu-central-1:123456789012:table/Music/import/01576624066799-e5f6a7b8',
            $import->importArn,
        );
        self::assertSame(ImportStatus::COMPLETED, $import->importStatus);
        self::assertSame(InputFormat::CSV, $import->inputFormat);
        self::assertSame(self::TABLE_ARN, $import->tableArn);

        $source = $import->s3BucketSource;

        self::assertInstanceOf(S3BucketSource::class, $source);
        self::assertSame('music-imports', $source->s3Bucket);
        self::assertSame('123456789012', $source->s3BucketOwner);
        self::assertSame('imports/music', $source->s3KeyPrefix);

        $startTime = $import->startTime;
        $endTime = $import->endTime;

        self::assertInstanceOf(DateTimeImmutable::class, $startTime);
        self::assertInstanceOf(DateTimeImmutable::class, $endTime);
        self::assertSame('2019-12-17T23:07:50.250000+00:00', $startTime->format('Y-m-d\TH:i:s.uP'));
        self::assertSame('2019-12-17T23:12:46.500000+00:00', $endTime->format('Y-m-d\TH:i:s.uP'));
    }

    /**
     * An import in progress has no end time.
     *
     * @throws ExceptionInterface
     * @throws InvalidArgumentException
     * @throws MissingCredentialsException
     * @throws NotFoundException
     */
    public function testReturnsTheSummaryOfAnImportInProgress(): void
    {
        $httpClient = new MockClient();
        $httpClient->addResponse($this->documentedResponse());

        $import = $this->createClient($httpClient)->listImports()->importSummaryList->get(1);

        self::assertInstanceOf(ImportSummary::class, $import);
        self::assertSame(ImportStatus::IN_PROGRESS, $import->importStatus);
        self::assertSame(InputFormat::DYNAMODB_JSON, $import->inputFormat);
        self::assertNull($import->endTime);

        $source = $import->s3BucketSource;

        self::assertInstanceOf(S3BucketSource::class, $source);
        self::assertNull($source->s3BucketOwner);
        self::assertNull($source->s3KeyPrefix);
    }

    /**
     * @throws ExceptionInterface
     * @throws InvalidArgumentException
     * @throws MissingCredentialsException
     * @throws NotFoundException
     */
    public function testReturnsWhereTheNextPageStarts(): void
    {
        $httpClient = new MockClient();
        $httpClient->addResponse($this->documentedResponse());

        $response = $this->createClient($httpClient)->listImports();

        self::assertSame(
            'fedcba9876543210fedcba987654320ffedcba987654320efedcba987654320d'
            . 'fedcba987654320cfedcba987654320bfedcba987654320a',
            $response->nextToken,
        );
    }

    /**
     * The last page has no `NextToken`.
     *
     * @throws ExceptionInterface
     * @throws InvalidArgumentException
     * @throws MissingCredentialsException
     * @throws NotFoundException
     */
    public function testLeavesTheElementsTheServiceOmitsNull(): void
    {
        $httpClient = new MockClient();
        $httpClient->addResponse(new Response(body: '{"ImportSummaryList":[{"ImportStatus":"CANCELLED"}]}'));

        $response = $this->createClient($httpClient)->listImports();

        self::assertNull($response->nextToken);

        $import = $response->importSummaryList->get(0);

        self::assertInstanceOf(ImportSummary::class, $import);
        self::assertSame(ImportStatus::CANCELLED, $import->importStatus);
        self::assertNull($import->cloudWatchLogGroupArn);
        self::assertNull($import->endTime);
        self::assertNull($import->importArn);
        self::assertNull($import->inputFormat);
        self::assertNull($import->s3BucketSource);
        self::assertNull($import->startTime);
        self::assertNull($import->tableArn);
    }

    /**
     * @throws ExceptionInterface
     * @throws InvalidArgumentException
     * @throws MissingCredentialsException
     * @throws NotFoundException
     */
    public function testReturnsAnEmptyListWhenTheServiceOmitsTheImportSummaryList(): void
    {
        $httpClient = new MockClient();
        $httpClient->addResponse(new Response(body: '{}'));

        $response = $this->createClient($httpClient)->listImports();

        self::assertTrue($response->importSummaryList->isEmpty());
        self::assertNull($response->nextToken);
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
        $httpClient->addResponse(new Response(body: '{"ImportSummaryList":[{"InputFormat":"XML"}]}'));

        $this->expectException(ResponseDeserializationException::class);

        $this->createClient($httpClient)->listImports();
    }

    /**
     * @throws InvalidArgumentException
     */
    public function testRejectsANextTokenShorterThanTheServiceAllows(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new ListImportsRequest(nextToken: str_repeat('0123456789abcdef', 6));
    }

    /**
     * @throws InvalidArgumentException
     */
    public function testRejectsANextTokenLongerThanTheServiceAllows(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new ListImportsRequest(nextToken: str_repeat('0123456789abcdef', 65));
    }

    /**
     * @throws InvalidArgumentException
     */
    public function testRejectsANextTokenThatIsNotLowercaseHexadecimal(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new ListImportsRequest(nextToken: str_repeat('0123456789ABCDEF', 7));
    }

    /**
     * A token is made of whole blocks of sixteen digits.
     *
     * @throws InvalidArgumentException
     */
    public function testRejectsANextTokenOfAPartialBlock(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new ListImportsRequest(nextToken: self::NEXT_TOKEN . '0');
    }

    /**
     * @throws InvalidArgumentException
     */
    public function testRejectsAPageSizeAboveTwentyFive(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new ListImportsRequest(pageSize: 26);
    }

    /**
     * @throws InvalidArgumentException
     */
    public function testRejectsATableArnLongerThanTheServiceAllows(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new ListImportsRequest(tableArn: str_repeat('a', 1025));
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
