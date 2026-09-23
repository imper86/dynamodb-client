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
use Imper86\DynamoDBClient\Message\UpdateKinesisStreamingDestinationRequest;
use Imper86\DynamoDBClient\Model\ApproximateCreationDateTimePrecision;
use Imper86\DynamoDBClient\Model\Credentials;
use Imper86\DynamoDBClient\Model\DestinationStatus;
use Imper86\DynamoDBClient\Model\UpdateKinesisStreamingConfiguration;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use JsonException;

use function file_get_contents;
use function json_decode;
use function str_repeat;

use const JSON_THROW_ON_ERROR;

/**
 * The UpdateKinesisStreamingDestination reference has no Examples section, so the fixtures are built
 * from its request and response syntax, switching the records a `Music` table puts on `MusicStream` to
 * microsecond timestamps.
 *
 * @see https://docs.aws.amazon.com/amazondynamodb/latest/APIReference/API_UpdateKinesisStreamingDestination.html
 * @internal
 */
#[CoversClass(DynamoDBClient::class)]
final class UpdateKinesisStreamingDestinationTest extends TestCase
{
    private const string REQUEST_FIXTURE = __DIR__ . '/fixtures/update-kinesis-streaming-destination-request.json';

    private const string RESPONSE_FIXTURE = __DIR__ . '/fixtures/update-kinesis-streaming-destination-response.json';

    private const string STREAM_ARN = 'arn:aws:kinesis:us-west-2:123456789012:stream/MusicStream';

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

        $this->createClient($httpClient)->updateKinesisStreamingDestination($this->documentedRequest());

        $sent = $httpClient->getLastRequest();

        self::assertInstanceOf(Request::class, $sent);
        self::assertSame(
            'DynamoDB_20120810.UpdateKinesisStreamingDestination',
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

        $this->createClient($httpClient)->updateKinesisStreamingDestination(
            new UpdateKinesisStreamingDestinationRequest(self::STREAM_ARN, 'Music'),
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

        $response = $this->createClient($httpClient)->updateKinesisStreamingDestination($this->documentedRequest());

        self::assertSame(DestinationStatus::UPDATING, $response->destinationStatus);
        self::assertSame(self::STREAM_ARN, $response->streamArn);
        self::assertSame('Music', $response->tableName);

        $configuration = $response->updateKinesisStreamingConfiguration;

        self::assertInstanceOf(UpdateKinesisStreamingConfiguration::class, $configuration);
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

        $response = $this->createClient($httpClient)->updateKinesisStreamingDestination($this->documentedRequest());

        self::assertNull($response->destinationStatus);
        self::assertNull($response->updateKinesisStreamingConfiguration);
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

        $this->createClient($httpClient)->updateKinesisStreamingDestination($this->documentedRequest());
    }

    /**
     * @throws InvalidArgumentException
     */
    public function testRejectsAStreamArnShorterThanTheServiceAllows(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new UpdateKinesisStreamingDestinationRequest(str_repeat('a', 36), 'Music');
    }

    /**
     * @throws InvalidArgumentException
     */
    public function testRejectsAStreamArnLongerThanTheServiceAllows(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new UpdateKinesisStreamingDestinationRequest(str_repeat('a', 1025), 'Music');
    }

    /**
     * @throws InvalidArgumentException
     */
    public function testRejectsATableNameLongerThanTheServiceAllows(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new UpdateKinesisStreamingDestinationRequest(self::STREAM_ARN, str_repeat('a', 1025));
    }

    /**
     * @throws InvalidArgumentException
     */
    private function documentedRequest(): UpdateKinesisStreamingDestinationRequest
    {
        return UpdateKinesisStreamingDestinationRequest::precision(
            self::STREAM_ARN,
            'Music',
            ApproximateCreationDateTimePrecision::MICROSECOND,
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
