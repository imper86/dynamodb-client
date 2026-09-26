<?php

declare(strict_types=1);

namespace Imper86\DynamoDBClientTests;

use Http\Discovery\Exception\NotFoundException;
use Http\Mock\Client as MockClient;
use Imper86\DynamoDBClient\DynamoDBClient;
use Imper86\DynamoDBClient\Exception\ExceptionInterface;
use Imper86\DynamoDBClient\Exception\MissingCredentialsException;
use Imper86\DynamoDBClient\Exception\ResponseDeserializationException;
use Imper86\DynamoDBClient\Message\ListExportsRequest;
use Imper86\DynamoDBClient\Model\Credentials;
use Imper86\DynamoDBClient\Model\ExportStatus;
use Imper86\DynamoDBClient\Model\ExportSummary;
use Imper86\DynamoDBClient\Model\ExportType;
use InvalidArgumentException;
use Nyholm\Psr7\Request;
use Nyholm\Psr7\Response;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

use function file_get_contents;
use function str_repeat;

/**
 * The ListExports reference has no Examples section, so the fixtures are built from its request and
 * response syntax: a page of the exports of a `Music` table, a completed full export and an incremental
 * one still in progress.
 *
 * @see https://docs.aws.amazon.com/amazondynamodb/latest/APIReference/API_ListExports.html
 * @internal
 */
#[CoversClass(DynamoDBClient::class)]
final class ListExportsTest extends TestCase
{
    private const REQUEST_FIXTURE = __DIR__ . '/fixtures/list-exports-request.json';

    private const RESPONSE_FIXTURE = __DIR__ . '/fixtures/list-exports-response.json';

    private const TABLE_ARN = 'arn:aws:dynamodb:eu-central-1:123456789012:table/Music';

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

        $this->createClient($httpClient)->listExports(new ListExportsRequest(
            maxResults: 2,
            nextToken: 'eyJFeHBvcnRBcm4iOiJhcm46YXdzOmR5bmFtb2RiIn0',
            tableArn: self::TABLE_ARN,
        ));

        $sent = $httpClient->getLastRequest();

        self::assertInstanceOf(Request::class, $sent);
        self::assertSame('DynamoDB_20120810.ListExports', $sent->getHeaderLine('X-Amz-Target'));
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

        $this->createClient($httpClient)->listExports();

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
    public function testReturnsTheSummaryOfEveryExport(): void
    {
        $httpClient = new MockClient();
        $httpClient->addResponse($this->documentedResponse());

        $summaries = $this->createClient($httpClient)->listExports()->exportSummaries;

        self::assertCount(2, $summaries);

        $full = $summaries->get(0);

        self::assertInstanceOf(ExportSummary::class, $full);
        self::assertSame(
            'arn:aws:dynamodb:eu-central-1:123456789012:table/Music/export/01576624066799-a1b2c3d4',
            $full->exportArn,
        );
        self::assertSame(ExportStatus::COMPLETED, $full->exportStatus);
        self::assertSame(ExportType::FULL_EXPORT, $full->exportType);

        $incremental = $summaries->get(1);

        self::assertInstanceOf(ExportSummary::class, $incremental);
        self::assertSame(ExportStatus::IN_PROGRESS, $incremental->exportStatus);
        self::assertSame(ExportType::INCREMENTAL_EXPORT, $incremental->exportType);
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

        $response = $this->createClient($httpClient)->listExports();

        self::assertSame('eyJFeHBvcnRBcm4iOiJhcm46YXdzOmR5bmFtb2RiOmV1In0', $response->nextToken);
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
        $httpClient->addResponse(new Response(body: '{"ExportSummaries":[{"ExportStatus":"FAILED"}]}'));

        $response = $this->createClient($httpClient)->listExports();

        self::assertNull($response->nextToken);

        $summary = $response->exportSummaries->get(0);

        self::assertInstanceOf(ExportSummary::class, $summary);
        self::assertSame(ExportStatus::FAILED, $summary->exportStatus);
        self::assertNull($summary->exportArn);
        self::assertNull($summary->exportType);
    }

    /**
     * @throws ExceptionInterface
     * @throws InvalidArgumentException
     * @throws MissingCredentialsException
     * @throws NotFoundException
     */
    public function testReturnsAnEmptyListWhenTheServiceOmitsTheExportSummaries(): void
    {
        $httpClient = new MockClient();
        $httpClient->addResponse(new Response(body: '{}'));

        $response = $this->createClient($httpClient)->listExports();

        self::assertTrue($response->exportSummaries->isEmpty());
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
        $httpClient->addResponse(new Response(body: '{"ExportSummaries":[{"ExportType":"PARTIAL_EXPORT"}]}'));

        $this->expectException(ResponseDeserializationException::class);

        $this->createClient($httpClient)->listExports();
    }

    /**
     * @throws InvalidArgumentException
     */
    public function testRejectsMaxResultsAboveTwentyFive(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new ListExportsRequest(maxResults: 26);
    }

    /**
     * @throws InvalidArgumentException
     */
    public function testRejectsATableArnLongerThanTheServiceAllows(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new ListExportsRequest(tableArn: str_repeat('a', 1025));
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
