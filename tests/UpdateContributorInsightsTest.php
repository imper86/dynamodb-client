<?php

declare(strict_types=1);

namespace Imper86\DynamoDBClientTests;

use Http\Discovery\Exception\NotFoundException;
use Http\Mock\Client as MockClient;
use Imper86\DynamoDBClient\DynamoDBClient;
use Imper86\DynamoDBClient\Exception\ExceptionInterface;
use Imper86\DynamoDBClient\Exception\MissingCredentialsException;
use Imper86\DynamoDBClient\Exception\ResponseDeserializationException;
use Imper86\DynamoDBClient\Message\UpdateContributorInsightsRequest;
use Imper86\DynamoDBClient\Model\ContributorInsightsMode;
use Imper86\DynamoDBClient\Model\ContributorInsightsStatus;
use Imper86\DynamoDBClient\Model\Credentials;
use InvalidArgumentException;
use Nyholm\Psr7\Request;
use Nyholm\Psr7\Response;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

use function file_get_contents;
use function str_repeat;

/**
 * The UpdateContributorInsights reference has no Examples section, so the fixtures are built from its
 * request and response syntax, enabling Contributor Insights for throttled keys on the `AlbumTitleIndex`
 * index of a `Music` table.
 *
 * @see https://docs.aws.amazon.com/amazondynamodb/latest/APIReference/API_UpdateContributorInsights.html
 * @internal
 */
#[CoversClass(DynamoDBClient::class)]
final class UpdateContributorInsightsTest extends TestCase
{
    private const string REQUEST_FIXTURE = __DIR__ . '/fixtures/update-contributor-insights-request.json';

    private const string RESPONSE_FIXTURE = __DIR__ . '/fixtures/update-contributor-insights-response.json';

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

        $this->createClient($httpClient)->updateContributorInsights($this->documentedRequest());

        $sent = $httpClient->getLastRequest();

        self::assertInstanceOf(Request::class, $sent);
        self::assertSame('DynamoDB_20120810.UpdateContributorInsights', $sent->getHeaderLine('X-Amz-Target'));
        self::assertJsonStringEqualsJsonFile(self::REQUEST_FIXTURE, $sent->getBody()->__toString());
    }

    /**
     * @throws ExceptionInterface
     * @throws InvalidArgumentException
     * @throws MissingCredentialsException
     * @throws NotFoundException
     */
    public function testReturnsTheNewStatus(): void
    {
        $httpClient = new MockClient();
        $httpClient->addResponse($this->documentedResponse());

        $response = $this->createClient($httpClient)->updateContributorInsights($this->documentedRequest());

        self::assertSame(ContributorInsightsMode::THROTTLED_KEYS, $response->contributorInsightsMode);
        self::assertSame(ContributorInsightsStatus::ENABLING, $response->contributorInsightsStatus);
        self::assertSame('AlbumTitleIndex', $response->indexName);
        self::assertSame('Music', $response->tableName);
    }

    /**
     * @throws ExceptionInterface
     * @throws InvalidArgumentException
     * @throws MissingCredentialsException
     * @throws NotFoundException
     */
    public function testLeavesTheElementsTheServiceOmitsNull(): void
    {
        $httpClient = new MockClient();
        $httpClient->addResponse(new Response(body: '{}'));

        $response = $this->createClient($httpClient)->updateContributorInsights($this->documentedRequest());

        self::assertNull($response->contributorInsightsMode);
        self::assertNull($response->contributorInsightsStatus);
        self::assertNull($response->indexName);
        self::assertNull($response->tableName);
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

        $this->createClient($httpClient)->updateContributorInsights($this->documentedRequest());
    }

    /**
     * @throws InvalidArgumentException
     */
    public function testRejectsATableNameLongerThanTheServiceAllows(): void
    {
        $this->expectException(InvalidArgumentException::class);

        UpdateContributorInsightsRequest::disable(str_repeat('a', 1025));
    }

    /**
     * @throws InvalidArgumentException
     */
    public function testRejectsAnIndexNameShorterThanTheServiceAllows(): void
    {
        $this->expectException(InvalidArgumentException::class);

        UpdateContributorInsightsRequest::disable('Music', 'ix');
    }

    /**
     * @throws InvalidArgumentException
     */
    public function testRejectsAnIndexNameLongerThanTheServiceAllows(): void
    {
        $this->expectException(InvalidArgumentException::class);

        UpdateContributorInsightsRequest::disable('Music', str_repeat('a', 256));
    }

    /**
     * @throws InvalidArgumentException
     */
    public function testRejectsAnIndexNameWithCharactersTheServiceDoesNotAllow(): void
    {
        $this->expectException(InvalidArgumentException::class);

        UpdateContributorInsightsRequest::disable('Music', 'Album Title Index');
    }

    /**
     * @throws InvalidArgumentException
     */
    private function documentedRequest(): UpdateContributorInsightsRequest
    {
        return UpdateContributorInsightsRequest::enable(
            'Music',
            ContributorInsightsMode::THROTTLED_KEYS,
            'AlbumTitleIndex',
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
