<?php

declare(strict_types=1);

namespace Imper86\DynamoDBClientTests;

use Http\Discovery\Exception\NotFoundException;
use Http\Mock\Client as MockClient;
use Imper86\DynamoDBClient\DynamoDBClient;
use Imper86\DynamoDBClient\Exception\ExceptionInterface;
use Imper86\DynamoDBClient\Exception\MissingCredentialsException;
use Imper86\DynamoDBClient\Exception\ResponseDeserializationException;
use Imper86\DynamoDBClient\Model\Credentials;
use Imper86\DynamoDBClient\Model\Endpoint;
use InvalidArgumentException;
use Nyholm\Psr7\Request;
use Nyholm\Psr7\Response;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

use function file_get_contents;

/**
 * The DescribeEndpoints reference has no Examples section, so the fixtures are built from its response
 * syntax, describing the single regional endpoint of `eu-central-1`. The operation takes no parameters,
 * so the client method takes no request object and the request fixture is an empty JSON object.
 *
 * @see https://docs.aws.amazon.com/amazondynamodb/latest/APIReference/API_DescribeEndpoints.html
 * @internal
 */
#[CoversClass(DynamoDBClient::class)]
final class DescribeEndpointsTest extends TestCase
{
    private const REQUEST_FIXTURE = __DIR__ . '/fixtures/describe-endpoints-request.json';

    private const RESPONSE_FIXTURE = __DIR__ . '/fixtures/describe-endpoints-response.json';

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

        $this->createClient($httpClient)->describeEndpoints();

        $sent = $httpClient->getLastRequest();

        self::assertInstanceOf(Request::class, $sent);
        self::assertSame('DynamoDB_20120810.DescribeEndpoints', $sent->getHeaderLine('X-Amz-Target'));
        self::assertJsonStringEqualsJsonFile(self::REQUEST_FIXTURE, $sent->getBody()->__toString());
    }

    /**
     * @throws ExceptionInterface
     * @throws InvalidArgumentException
     * @throws MissingCredentialsException
     * @throws NotFoundException
     */
    public function testReturnsTheEndpoints(): void
    {
        $httpClient = new MockClient();
        $httpClient->addResponse($this->documentedResponse());

        $endpoints = $this->createClient($httpClient)->describeEndpoints()->endpoints;

        self::assertCount(1, $endpoints);

        $endpoint = $endpoints->get(0);

        self::assertInstanceOf(Endpoint::class, $endpoint);
        self::assertSame('dynamodb.eu-central-1.amazonaws.com', $endpoint->address);
        self::assertSame(1440, $endpoint->cachePeriodInMinutes);
    }

    /**
     * @throws ExceptionInterface
     * @throws InvalidArgumentException
     * @throws MissingCredentialsException
     * @throws NotFoundException
     */
    public function testReturnsNoEndpointsWhenTheServiceOmitsThem(): void
    {
        $httpClient = new MockClient();
        $httpClient->addResponse(new Response(body: '{}'));

        $response = $this->createClient($httpClient)->describeEndpoints();

        self::assertTrue($response->endpoints->isEmpty());
    }

    /**
     * @throws ExceptionInterface
     * @throws InvalidArgumentException
     * @throws MissingCredentialsException
     * @throws NotFoundException
     */
    public function testLeavesEndpointMembersNullWhenTheServiceOmitsThem(): void
    {
        $httpClient = new MockClient();
        $httpClient->addResponse(new Response(body: '{"Endpoints":[{}]}'));

        $endpoint = $this->createClient($httpClient)->describeEndpoints()->endpoints->get(0);

        self::assertInstanceOf(Endpoint::class, $endpoint);
        self::assertNull($endpoint->address);
        self::assertNull($endpoint->cachePeriodInMinutes);
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
        $httpClient->addResponse(new Response(body: '{"Endpoints":[{"CachePeriodInMinutes":"one day"}]}'));

        $this->expectException(ResponseDeserializationException::class);

        $this->createClient($httpClient)->describeEndpoints();
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
