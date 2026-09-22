<?php

declare(strict_types=1);

namespace Imper86\DynamoDBClientTests;

use Http\Discovery\Exception\NotFoundException;
use Http\Mock\Client as MockClient;
use InvalidArgumentException;
use Nyholm\Psr7\Request;
use Nyholm\Psr7\Response;
use Imper86\DynamoDBClient\DynamoDBClient;
use Imper86\DynamoDBClient\Exception\ExceptionInterface;
use Imper86\DynamoDBClient\Exception\MissingCredentialsException;
use Imper86\DynamoDBClient\Exception\ResponseDeserializationException;
use Imper86\DynamoDBClient\Message\DescribeTimeToLiveRequest;
use Imper86\DynamoDBClient\Model\Credentials;
use Imper86\DynamoDBClient\Model\TimeToLiveDescription;
use Imper86\DynamoDBClient\Model\TimeToLiveStatus;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

use function file_get_contents;
use function str_repeat;

/**
 * The DescribeTimeToLive reference has no Examples section, so the fixtures are built from its request
 * and response syntax, describing a `Music` table that expires items by their `ExpiresAt` attribute.
 *
 * @see https://docs.aws.amazon.com/amazondynamodb/latest/APIReference/API_DescribeTimeToLive.html
 * @internal
 */
#[CoversClass(DynamoDBClient::class)]
final class DescribeTimeToLiveTest extends TestCase
{
    private const string REQUEST_FIXTURE = __DIR__ . '/fixtures/describe-time-to-live-request.json';

    private const string RESPONSE_FIXTURE = __DIR__ . '/fixtures/describe-time-to-live-response.json';

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

        $this->createClient($httpClient)->describeTimeToLive(new DescribeTimeToLiveRequest('Music'));

        $sent = $httpClient->getLastRequest();

        self::assertInstanceOf(Request::class, $sent);
        self::assertSame('DynamoDB_20120810.DescribeTimeToLive', $sent->getHeaderLine('X-Amz-Target'));
        self::assertJsonStringEqualsJsonFile(self::REQUEST_FIXTURE, $sent->getBody()->__toString());
    }

    /**
     * @throws ExceptionInterface
     * @throws InvalidArgumentException
     * @throws MissingCredentialsException
     * @throws NotFoundException
     */
    public function testReturnsTheTimeToLiveSettingsOfTheTable(): void
    {
        $httpClient = new MockClient();
        $httpClient->addResponse($this->documentedResponse());

        $response = $this->createClient($httpClient)->describeTimeToLive(new DescribeTimeToLiveRequest('Music'));

        $description = $response->timeToLiveDescription;

        self::assertInstanceOf(TimeToLiveDescription::class, $description);
        self::assertSame('ExpiresAt', $description->attributeName);
        self::assertSame(TimeToLiveStatus::ENABLED, $description->timeToLiveStatus);
    }

    /**
     * A table that never had time to live enabled reports only its status.
     *
     * @throws ExceptionInterface
     * @throws InvalidArgumentException
     * @throws MissingCredentialsException
     * @throws NotFoundException
     */
    public function testLeavesTheAttributeNameNullWhenTheServiceOmitsIt(): void
    {
        $httpClient = new MockClient();
        $httpClient->addResponse(new Response(body: '{"TimeToLiveDescription":{"TimeToLiveStatus":"DISABLED"}}'));

        $response = $this->createClient($httpClient)->describeTimeToLive(new DescribeTimeToLiveRequest('Music'));

        $description = $response->timeToLiveDescription;

        self::assertInstanceOf(TimeToLiveDescription::class, $description);
        self::assertNull($description->attributeName);
        self::assertSame(TimeToLiveStatus::DISABLED, $description->timeToLiveStatus);
    }

    /**
     * @throws ExceptionInterface
     * @throws InvalidArgumentException
     * @throws MissingCredentialsException
     * @throws NotFoundException
     */
    public function testLeavesTheTimeToLiveDescriptionNullWhenTheServiceOmitsIt(): void
    {
        $httpClient = new MockClient();
        $httpClient->addResponse(new Response(body: '{}'));

        $response = $this->createClient($httpClient)->describeTimeToLive(new DescribeTimeToLiveRequest('Music'));

        self::assertNull($response->timeToLiveDescription);
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
        $httpClient->addResponse(new Response(body: '{"TimeToLiveDescription":{"TimeToLiveStatus":"PAUSED"}}'));

        $this->expectException(ResponseDeserializationException::class);

        $this->createClient($httpClient)->describeTimeToLive(new DescribeTimeToLiveRequest('Music'));
    }

    /**
     * @throws InvalidArgumentException
     */
    public function testRejectsATableNameLongerThanTheServiceAllows(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new DescribeTimeToLiveRequest(str_repeat('a', 1025));
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
