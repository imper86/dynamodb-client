<?php

declare(strict_types=1);

namespace Imper86\DynamoDBClientTests;

use Http\Discovery\Exception\NotFoundException;
use Http\Mock\Client as MockClient;
use Imper86\DynamoDBClient\DynamoDBClient;
use Imper86\DynamoDBClient\Exception\ExceptionInterface;
use Imper86\DynamoDBClient\Exception\MissingCredentialsException;
use Imper86\DynamoDBClient\Exception\ResponseDeserializationException;
use Imper86\DynamoDBClient\Message\DescribeKinesisStreamingDestinationRequest;
use Imper86\DynamoDBClient\Model\ApproximateCreationDateTimePrecision;
use Imper86\DynamoDBClient\Model\Credentials;
use Imper86\DynamoDBClient\Model\DestinationStatus;
use Imper86\DynamoDBClient\Model\KinesisDataStreamDestination;
use InvalidArgumentException;
use Nyholm\Psr7\Request;
use Nyholm\Psr7\Response;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

use function file_get_contents;
use function str_repeat;

/**
 * The DescribeKinesisStreamingDestination reference has no Examples section, so the fixtures are built
 * from its request and response syntax, describing a `Music` table streaming to one active Kinesis data
 * stream, with a second that failed to enable.
 *
 * @see https://docs.aws.amazon.com/amazondynamodb/latest/APIReference/API_DescribeKinesisStreamingDestination.html
 * @internal
 */
#[CoversClass(DynamoDBClient::class)]
final class DescribeKinesisStreamingDestinationTest extends TestCase
{
    private const string REQUEST_FIXTURE =
        __DIR__ . '/fixtures/describe-kinesis-streaming-destination-request.json';

    private const string RESPONSE_FIXTURE =
        __DIR__ . '/fixtures/describe-kinesis-streaming-destination-response.json';

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

        $this->createClient($httpClient)
            ->describeKinesisStreamingDestination(new DescribeKinesisStreamingDestinationRequest('Music'))
        ;

        $sent = $httpClient->getLastRequest();

        self::assertInstanceOf(Request::class, $sent);
        self::assertSame(
            'DynamoDB_20120810.DescribeKinesisStreamingDestination',
            $sent->getHeaderLine('X-Amz-Target'),
        );
        self::assertJsonStringEqualsJsonFile(self::REQUEST_FIXTURE, $sent->getBody()->__toString());
    }

    /**
     * @throws ExceptionInterface
     * @throws InvalidArgumentException
     * @throws MissingCredentialsException
     * @throws NotFoundException
     */
    public function testReturnsTheDestinationsOfTheTable(): void
    {
        $httpClient = new MockClient();
        $httpClient->addResponse($this->documentedResponse());

        $response = $this->createClient($httpClient)
            ->describeKinesisStreamingDestination(new DescribeKinesisStreamingDestinationRequest('Music'))
        ;

        self::assertSame('Music', $response->tableName);
        self::assertCount(2, $response->kinesisDataStreamDestinations);

        $active = $response->kinesisDataStreamDestinations->get(0);

        self::assertInstanceOf(KinesisDataStreamDestination::class, $active);
        self::assertSame(ApproximateCreationDateTimePrecision::MICROSECOND, $active->approximateCreationDateTimePrecision);
        self::assertSame(DestinationStatus::ACTIVE, $active->destinationStatus);
        self::assertNull($active->destinationStatusDescription);
        self::assertSame('arn:aws:kinesis:eu-central-1:123456789012:stream/music-changes', $active->streamArn);
    }

    /**
     * @throws ExceptionInterface
     * @throws InvalidArgumentException
     * @throws MissingCredentialsException
     * @throws NotFoundException
     */
    public function testReturnsWhyADestinationFailedToEnable(): void
    {
        $httpClient = new MockClient();
        $httpClient->addResponse($this->documentedResponse());

        $failed = $this->createClient($httpClient)
            ->describeKinesisStreamingDestination(new DescribeKinesisStreamingDestinationRequest('Music'))
            ->kinesisDataStreamDestinations
            ->get(1)
        ;

        self::assertInstanceOf(KinesisDataStreamDestination::class, $failed);
        self::assertSame(DestinationStatus::ENABLE_FAILED, $failed->destinationStatus);
        self::assertSame(
            'User does not have a permission to put records to the Kinesis data stream',
            $failed->destinationStatusDescription,
        );
    }

    /**
     * @throws ExceptionInterface
     * @throws InvalidArgumentException
     * @throws MissingCredentialsException
     * @throws NotFoundException
     */
    public function testLeavesDestinationMembersNullWhenTheServiceOmitsThem(): void
    {
        $httpClient = new MockClient();
        $httpClient->addResponse(new Response(body: '{"KinesisDataStreamDestinations":[{}],"TableName":"Music"}'));

        $destination = $this->createClient($httpClient)
            ->describeKinesisStreamingDestination(new DescribeKinesisStreamingDestinationRequest('Music'))
            ->kinesisDataStreamDestinations
            ->get(0)
        ;

        self::assertInstanceOf(KinesisDataStreamDestination::class, $destination);
        self::assertNull($destination->approximateCreationDateTimePrecision);
        self::assertNull($destination->destinationStatus);
        self::assertNull($destination->destinationStatusDescription);
        self::assertNull($destination->streamArn);
    }

    /**
     * @throws ExceptionInterface
     * @throws InvalidArgumentException
     * @throws MissingCredentialsException
     * @throws NotFoundException
     */
    public function testReturnsNoDestinationsWhenTheServiceOmitsThem(): void
    {
        $httpClient = new MockClient();
        $httpClient->addResponse(new Response(body: '{}'));

        $response = $this->createClient($httpClient)
            ->describeKinesisStreamingDestination(new DescribeKinesisStreamingDestinationRequest('Music'))
        ;

        self::assertTrue($response->kinesisDataStreamDestinations->isEmpty());
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
        $httpClient->addResponse(
            new Response(body: '{"KinesisDataStreamDestinations":[{"DestinationStatus":"PAUSED"}]}'),
        );

        $this->expectException(ResponseDeserializationException::class);

        $this->createClient($httpClient)
            ->describeKinesisStreamingDestination(new DescribeKinesisStreamingDestinationRequest('Music'))
        ;
    }

    /**
     * @throws InvalidArgumentException
     */
    public function testRejectsATableNameLongerThanTheServiceAllows(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new DescribeKinesisStreamingDestinationRequest(str_repeat('a', 1025));
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
