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
use Imper86\DynamoDBClient\Message\DisableKinesisStreamingDestinationRequest;
use Imper86\DynamoDBClient\Model\ApproximateCreationDateTimePrecision;
use Imper86\DynamoDBClient\Model\Credentials;
use Imper86\DynamoDBClient\Model\DestinationStatus;
use Imper86\DynamoDBClient\Model\EnableKinesisStreamingConfiguration;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use JsonException;

use function file_get_contents;
use function json_decode;
use function str_repeat;

use const JSON_THROW_ON_ERROR;

/**
 * The DisableKinesisStreamingDestination reference has no Examples section, so the fixtures are built
 * from its request and response syntax, stopping a `Music` table from streaming to `MusicStream`.
 *
 * @see https://docs.aws.amazon.com/amazondynamodb/latest/APIReference/API_DisableKinesisStreamingDestination.html
 * @internal
 */
#[CoversClass(DynamoDBClient::class)]
final class DisableKinesisStreamingDestinationTest extends TestCase
{
    private const REQUEST_FIXTURE = __DIR__ . '/fixtures/disable-kinesis-streaming-destination-request.json';

    private const RESPONSE_FIXTURE = __DIR__ . '/fixtures/disable-kinesis-streaming-destination-response.json';

    private const STREAM_ARN = 'arn:aws:kinesis:us-west-2:123456789012:stream/MusicStream';

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

        $this->createClient($httpClient)->disableKinesisStreamingDestination($this->documentedRequest());

        $sent = $httpClient->getLastRequest();

        self::assertInstanceOf(Request::class, $sent);
        self::assertSame(
            'DynamoDB_20120810.DisableKinesisStreamingDestination',
            $sent->getHeaderLine('X-Amz-Target'),
        );
        self::assertJsonStringEqualsJsonFile(self::REQUEST_FIXTURE, $sent->getBody()->__toString());
    }

    /**
     * @throws ExceptionInterface
     * @throws InvalidArgumentException
     * @throws MissingCredentialsException
     * @throws NotFoundException
     * @throws JsonException
     */
    public function testLeavesTheStreamingConfigurationOutWhenItIsNotGiven(): void
    {
        $httpClient = new MockClient();
        $httpClient->addResponse($this->documentedResponse());

        $this->createClient($httpClient)->disableKinesisStreamingDestination(
            new DisableKinesisStreamingDestinationRequest(self::STREAM_ARN, 'Music'),
        );

        $sent = $httpClient->getLastRequest();

        self::assertInstanceOf(Request::class, $sent);
        self::assertSame(
            ['StreamArn' => self::STREAM_ARN, 'TableName' => 'Music'],
            json_decode($sent->getBody()->__toString(), true, flags: JSON_THROW_ON_ERROR),
        );
    }

    /**
     * @throws ExceptionInterface
     * @throws InvalidArgumentException
     * @throws MissingCredentialsException
     * @throws NotFoundException
     */
    public function testReturnsTheStatusOfTheDestination(): void
    {
        $httpClient = new MockClient();
        $httpClient->addResponse($this->documentedResponse());

        $response = $this->createClient($httpClient)->disableKinesisStreamingDestination($this->documentedRequest());

        self::assertSame(DestinationStatus::DISABLING, $response->destinationStatus);
        self::assertSame(self::STREAM_ARN, $response->streamArn);
        self::assertSame('Music', $response->tableName);

        $configuration = $response->enableKinesisStreamingConfiguration;

        self::assertInstanceOf(EnableKinesisStreamingConfiguration::class, $configuration);
        self::assertSame(
            ApproximateCreationDateTimePrecision::MICROSECOND,
            $configuration->approximateCreationDateTimePrecision,
        );
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

        $response = $this->createClient($httpClient)->disableKinesisStreamingDestination($this->documentedRequest());

        self::assertNull($response->destinationStatus);
        self::assertNull($response->enableKinesisStreamingConfiguration);
        self::assertNull($response->streamArn);
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
        $httpClient->addResponse(new Response(body: '{"DestinationStatus":"PAUSED"}'));

        $this->expectException(ResponseDeserializationException::class);

        $this->createClient($httpClient)->disableKinesisStreamingDestination($this->documentedRequest());
    }

    /**
     * @throws InvalidArgumentException
     */
    public function testRejectsAStreamArnShorterThanTheServiceAllows(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new DisableKinesisStreamingDestinationRequest(str_repeat('a', 36), 'Music');
    }

    /**
     * @throws InvalidArgumentException
     */
    public function testRejectsAStreamArnLongerThanTheServiceAllows(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new DisableKinesisStreamingDestinationRequest(str_repeat('a', 1025), 'Music');
    }

    /**
     * @throws InvalidArgumentException
     */
    public function testRejectsATableNameLongerThanTheServiceAllows(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new DisableKinesisStreamingDestinationRequest(self::STREAM_ARN, str_repeat('a', 1025));
    }

    /**
     * @throws InvalidArgumentException
     */
    private function documentedRequest(): DisableKinesisStreamingDestinationRequest
    {
        return new DisableKinesisStreamingDestinationRequest(
            streamArn: self::STREAM_ARN,
            tableName: 'Music',
            enableKinesisStreamingConfiguration: new EnableKinesisStreamingConfiguration(
                ApproximateCreationDateTimePrecision::MICROSECOND,
            ),
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
