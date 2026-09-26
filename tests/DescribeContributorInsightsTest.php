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
use Imper86\DynamoDBClient\Message\DescribeContributorInsightsRequest;
use Imper86\DynamoDBClient\Model\ContributorInsightsMode;
use Imper86\DynamoDBClient\Model\ContributorInsightsStatus;
use Imper86\DynamoDBClient\Model\Credentials;
use Imper86\DynamoDBClient\Model\FailureException;
use Imper86\DynamoDBClient\ValueObject\StringList;
use InvalidArgumentException;
use Nyholm\Psr7\Request;
use Nyholm\Psr7\Response;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

use function file_get_contents;
use function str_repeat;

/**
 * The DescribeContributorInsights reference has no Examples section, so the fixtures are built from its
 * request and response syntax, describing Contributor Insights enabled on the `AlbumTitleIndex` of the
 * `Music` table. The rule names follow the pattern CloudWatch gives the rules DynamoDB creates.
 *
 * @see https://docs.aws.amazon.com/amazondynamodb/latest/APIReference/API_DescribeContributorInsights.html
 * @internal
 */
#[CoversClass(DynamoDBClient::class)]
final class DescribeContributorInsightsTest extends TestCase
{
    private const REQUEST_FIXTURE = __DIR__ . '/fixtures/describe-contributor-insights-request.json';

    private const RESPONSE_FIXTURE = __DIR__ . '/fixtures/describe-contributor-insights-response.json';

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

        $this->createClient($httpClient)->describeContributorInsights($this->documentedRequest());

        $sent = $httpClient->getLastRequest();

        self::assertInstanceOf(Request::class, $sent);
        self::assertSame('DynamoDB_20120810.DescribeContributorInsights', $sent->getHeaderLine('X-Amz-Target'));
        self::assertJsonStringEqualsJsonFile(self::REQUEST_FIXTURE, $sent->getBody()->__toString());
    }

    /**
     * @throws ExceptionInterface
     * @throws InvalidArgumentException
     * @throws MissingCredentialsException
     * @throws NotFoundException
     */
    public function testLeavesOutTheIndexNameWhenDescribingTheTable(): void
    {
        $httpClient = new MockClient();
        $httpClient->addResponse($this->documentedResponse());

        $this->createClient($httpClient)->describeContributorInsights(new DescribeContributorInsightsRequest('Music'));

        $sent = $httpClient->getLastRequest();

        self::assertInstanceOf(Request::class, $sent);
        self::assertJsonStringEqualsJsonString('{"TableName":"Music"}', $sent->getBody()->__toString());
    }

    /**
     * @throws ExceptionInterface
     * @throws InvalidArgumentException
     * @throws MissingCredentialsException
     * @throws NotFoundException
     */
    public function testReturnsTheContributorInsightsSettings(): void
    {
        $httpClient = new MockClient();
        $httpClient->addResponse($this->documentedResponse());

        $response = $this->createClient($httpClient)->describeContributorInsights($this->documentedRequest());

        self::assertSame('Music', $response->tableName);
        self::assertSame('AlbumTitleIndex', $response->indexName);
        self::assertSame(ContributorInsightsStatus::ENABLED, $response->contributorInsightsStatus);
        self::assertSame(ContributorInsightsMode::ACCESSED_AND_THROTTLED_KEYS, $response->contributorInsightsMode);
        self::assertNull($response->failureException);

        $rules = $response->contributorInsightsRuleList;

        self::assertInstanceOf(StringList::class, $rules);
        self::assertSame([
            'DynamoDBContributorInsights-PKC-Music-AlbumTitleIndex-1576623000500',
            'DynamoDBContributorInsights-PKT-Music-AlbumTitleIndex-1576623000500',
        ], $rules->toArray());

        $lastUpdate = $response->lastUpdateDateTime;

        self::assertInstanceOf(DateTimeImmutable::class, $lastUpdate);
        self::assertSame('2019-12-17T23:07:46.799000+00:00', $lastUpdate->format('Y-m-d\TH:i:s.uP'));
    }

    /**
     * @throws ExceptionInterface
     * @throws InvalidArgumentException
     * @throws MissingCredentialsException
     * @throws NotFoundException
     */
    public function testReturnsTheFailureThatLeftContributorInsightsFailed(): void
    {
        $httpClient = new MockClient();
        $httpClient->addResponse(new Response(body: '{"ContributorInsightsStatus":"FAILED","TableName":"Music",'
            . '"FailureException":{"ExceptionName":"LimitExceededException",'
            . '"ExceptionDescription":"Per-account Amazon CloudWatch Contributor Insights rule limit reached."}}'));

        $response = $this->createClient($httpClient)
            ->describeContributorInsights(new DescribeContributorInsightsRequest('Music'))
        ;

        self::assertSame(ContributorInsightsStatus::FAILED, $response->contributorInsightsStatus);

        $failure = $response->failureException;

        self::assertInstanceOf(FailureException::class, $failure);
        self::assertSame('LimitExceededException', $failure->exceptionName);
        self::assertSame(
            'Per-account Amazon CloudWatch Contributor Insights rule limit reached.',
            $failure->exceptionDescription,
        );
    }

    /**
     * A table that never had Contributor Insights enabled reports its status and nothing else.
     *
     * @throws ExceptionInterface
     * @throws InvalidArgumentException
     * @throws MissingCredentialsException
     * @throws NotFoundException
     */
    public function testLeavesTheElementsTheServiceOmitsNull(): void
    {
        $httpClient = new MockClient();
        $httpClient->addResponse(
            new Response(body: '{"ContributorInsightsStatus":"DISABLED","TableName":"Music"}'),
        );

        $response = $this->createClient($httpClient)
            ->describeContributorInsights(new DescribeContributorInsightsRequest('Music'))
        ;

        self::assertSame(ContributorInsightsStatus::DISABLED, $response->contributorInsightsStatus);
        self::assertSame('Music', $response->tableName);
        self::assertNull($response->contributorInsightsMode);
        self::assertNull($response->contributorInsightsRuleList);
        self::assertNull($response->failureException);
        self::assertNull($response->indexName);
        self::assertNull($response->lastUpdateDateTime);
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
        $httpClient->addResponse(new Response(body: '{"ContributorInsightsStatus":"PAUSED"}'));

        $this->expectException(ResponseDeserializationException::class);

        $this->createClient($httpClient)->describeContributorInsights($this->documentedRequest());
    }

    /**
     * @throws InvalidArgumentException
     */
    public function testRejectsATableNameLongerThanTheServiceAllows(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new DescribeContributorInsightsRequest(str_repeat('a', 1025));
    }

    /**
     * @throws InvalidArgumentException
     */
    public function testRejectsAnIndexNameShorterThanThreeCharacters(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new DescribeContributorInsightsRequest(tableName: 'Music', indexName: 'Al');
    }

    /**
     * @throws InvalidArgumentException
     */
    public function testRejectsAnIndexNameLongerThanTwoHundredAndFiftyFiveCharacters(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new DescribeContributorInsightsRequest(tableName: 'Music', indexName: str_repeat('a', 256));
    }

    /**
     * @throws InvalidArgumentException
     */
    public function testRejectsAnIndexNameWithACharacterThePatternDoesNotAllow(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new DescribeContributorInsightsRequest(tableName: 'Music', indexName: 'Album Title Index');
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

    /**
     * @throws InvalidArgumentException
     */
    private function documentedRequest(): DescribeContributorInsightsRequest
    {
        return new DescribeContributorInsightsRequest(tableName: 'Music', indexName: 'AlbumTitleIndex');
    }

    private function documentedResponse(): Response
    {
        $body = file_get_contents(self::RESPONSE_FIXTURE);

        self::assertIsString($body);

        return new Response(body: $body);
    }
}
