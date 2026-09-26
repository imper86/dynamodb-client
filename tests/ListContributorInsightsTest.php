<?php

declare(strict_types=1);

namespace Imper86\DynamoDBClientTests;

use Http\Discovery\Exception\NotFoundException;
use Http\Mock\Client as MockClient;
use Imper86\DynamoDBClient\DynamoDBClient;
use Imper86\DynamoDBClient\Exception\ExceptionInterface;
use Imper86\DynamoDBClient\Exception\MissingCredentialsException;
use Imper86\DynamoDBClient\Exception\ResponseDeserializationException;
use Imper86\DynamoDBClient\Message\ListContributorInsightsRequest;
use Imper86\DynamoDBClient\Model\ContributorInsightsMode;
use Imper86\DynamoDBClient\Model\ContributorInsightsStatus;
use Imper86\DynamoDBClient\Model\ContributorInsightsSummary;
use Imper86\DynamoDBClient\Model\Credentials;
use InvalidArgumentException;
use Nyholm\Psr7\Request;
use Nyholm\Psr7\Response;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

use function file_get_contents;
use function str_repeat;

/**
 * The ListContributorInsights reference has no Examples section, so the fixtures are built from its
 * request and response syntax: a page of the summaries of a `Music` table, one for the table and one
 * for its `AlbumTitleIndex`.
 *
 * @see https://docs.aws.amazon.com/amazondynamodb/latest/APIReference/API_ListContributorInsights.html
 * @internal
 */
#[CoversClass(DynamoDBClient::class)]
final class ListContributorInsightsTest extends TestCase
{
    private const REQUEST_FIXTURE = __DIR__ . '/fixtures/list-contributor-insights-request.json';

    private const RESPONSE_FIXTURE = __DIR__ . '/fixtures/list-contributor-insights-response.json';

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

        $this->createClient($httpClient)->listContributorInsights(new ListContributorInsightsRequest(
            maxResults: 10,
            nextToken: 'eyJUYWJsZU5hbWUiOiJNdXNpYyJ9',
            tableName: 'Music',
        ));

        $sent = $httpClient->getLastRequest();

        self::assertInstanceOf(Request::class, $sent);
        self::assertSame('DynamoDB_20120810.ListContributorInsights', $sent->getHeaderLine('X-Amz-Target'));
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

        $this->createClient($httpClient)->listContributorInsights();

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
    public function testReturnsTheSummaryOfTheTableAndOfItsIndex(): void
    {
        $httpClient = new MockClient();
        $httpClient->addResponse($this->documentedResponse());

        $summaries = $this->createClient($httpClient)->listContributorInsights()->contributorInsightsSummaries;

        self::assertCount(2, $summaries);

        $table = $summaries->get(0);

        self::assertInstanceOf(ContributorInsightsSummary::class, $table);
        self::assertSame(ContributorInsightsMode::ACCESSED_AND_THROTTLED_KEYS, $table->contributorInsightsMode);
        self::assertSame(ContributorInsightsStatus::ENABLED, $table->contributorInsightsStatus);
        self::assertSame('Music', $table->tableName);
        self::assertNull($table->indexName);

        $index = $summaries->get(1);

        self::assertInstanceOf(ContributorInsightsSummary::class, $index);
        self::assertSame(ContributorInsightsMode::THROTTLED_KEYS, $index->contributorInsightsMode);
        self::assertSame(ContributorInsightsStatus::ENABLING, $index->contributorInsightsStatus);
        self::assertSame('AlbumTitleIndex', $index->indexName);
        self::assertSame('Music', $index->tableName);
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

        $response = $this->createClient($httpClient)->listContributorInsights();

        self::assertSame(
            'eyJUYWJsZU5hbWUiOiJNdXNpYyIsIkluZGV4TmFtZSI6IkFsYnVtVGl0bGVJbmRleCJ9',
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
        $httpClient->addResponse(new Response(body: '{"ContributorInsightsSummaries":[{"TableName":"Music"}]}'));

        $response = $this->createClient($httpClient)->listContributorInsights();

        self::assertNull($response->nextToken);

        $summary = $response->contributorInsightsSummaries->get(0);

        self::assertInstanceOf(ContributorInsightsSummary::class, $summary);
        self::assertSame('Music', $summary->tableName);
        self::assertNull($summary->contributorInsightsMode);
        self::assertNull($summary->contributorInsightsStatus);
        self::assertNull($summary->indexName);
    }

    /**
     * @throws ExceptionInterface
     * @throws InvalidArgumentException
     * @throws MissingCredentialsException
     * @throws NotFoundException
     */
    public function testReturnsAnEmptyListWhenTheServiceOmitsTheSummaries(): void
    {
        $httpClient = new MockClient();
        $httpClient->addResponse(new Response(body: '{}'));

        $response = $this->createClient($httpClient)->listContributorInsights();

        self::assertTrue($response->contributorInsightsSummaries->isEmpty());
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
        $httpClient->addResponse(new Response(
            body: '{"ContributorInsightsSummaries":[{"ContributorInsightsStatus":"PAUSED"}]}',
        ));

        $this->expectException(ResponseDeserializationException::class);

        $this->createClient($httpClient)->listContributorInsights();
    }

    /**
     * @throws InvalidArgumentException
     */
    public function testRejectsMaxResultsAboveOneHundred(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new ListContributorInsightsRequest(maxResults: 101);
    }

    /**
     * @throws InvalidArgumentException
     */
    public function testRejectsATableNameLongerThanTheServiceAllows(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new ListContributorInsightsRequest(tableName: str_repeat('a', 1025));
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
