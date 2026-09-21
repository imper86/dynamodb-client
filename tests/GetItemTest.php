<?php

declare(strict_types=1);

namespace Imper86\DynamoDBClientTests;

use Http\Discovery\Exception\NotFoundException;
use Http\Mock\Client as MockClient;
use InvalidArgumentException;
use Nyholm\Psr7\Request;
use Nyholm\Psr7\Response;
use Imper86\DynamoDBClient\DynamoDBClient;
use Imper86\DynamoDBClient\Exception\BadResponseException;
use Imper86\DynamoDBClient\Exception\ExceptionInterface;
use Imper86\DynamoDBClient\Exception\HttpClientException;
use Imper86\DynamoDBClient\Exception\InvalidArgumentException as ClientInvalidArgumentException;
use Imper86\DynamoDBClient\Exception\MissingCredentialsException;
use Imper86\DynamoDBClient\Exception\RequestSerializationException;
use Imper86\DynamoDBClient\Exception\ResponseDeserializationException;
use Imper86\DynamoDBClient\Message\GetItemRequest;
use Imper86\DynamoDBClient\Model\AttributeValue;
use Imper86\DynamoDBClient\Model\AttributeValueMap;
use Imper86\DynamoDBClient\Model\ConsumedCapacity;
use Imper86\DynamoDBClient\Model\Credentials;
use Imper86\DynamoDBClient\Model\ReturnConsumedCapacity;
use Imper86\DynamoDBClient\ValueObject\StringSet;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Message\RequestFactoryInterface;
use RuntimeException;
use Symfony\Component\Serializer\SerializerInterface;

use function file_get_contents;

/**
 * The messages exchanged here are the "Retrieve Item Attributes" example of the GetItem reference.
 *
 * @see https://docs.aws.amazon.com/amazondynamodb/latest/APIReference/API_GetItem.html
 * @internal
 */
#[CoversClass(DynamoDBClient::class)]
final class GetItemTest extends TestCase
{
    private const string REQUEST_FIXTURE = __DIR__ . '/fixtures/get-item-request.json';

    private const string RESPONSE_FIXTURE = __DIR__ . '/fixtures/get-item-response.json';

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

        $this->createClient($httpClient)->getItem($this->documentedRequest());

        $sent = $httpClient->getLastRequest();

        self::assertInstanceOf(Request::class, $sent);
        self::assertSame('POST', $sent->getMethod());
        self::assertSame('https://dynamodb.eu-central-1.amazonaws.com/', $sent->getUri()->__toString());
        self::assertSame('DynamoDB_20120810.GetItem', $sent->getHeaderLine('X-Amz-Target'));
        self::assertSame('application/x-amz-json-1.0', $sent->getHeaderLine('Content-Type'));
        self::assertSame('identity', $sent->getHeaderLine('Accept-Encoding'));
        self::assertStringStartsWith('AWS4-HMAC-SHA256 ', $sent->getHeaderLine('Authorization'));
        self::assertNotSame('', $sent->getHeaderLine('X-Amz-Date'));
        self::assertStringStartsWith('imper86-dynamodb-client/', $sent->getHeaderLine('User-Agent'));
        self::assertJsonStringEqualsJsonFile(self::REQUEST_FIXTURE, $sent->getBody()->__toString());
    }

    /**
     * @throws ExceptionInterface
     * @throws InvalidArgumentException
     * @throws MissingCredentialsException
     * @throws NotFoundException
     */
    public function testReturnsTheDeserializedItem(): void
    {
        $httpClient = new MockClient();
        $httpClient->addResponse($this->documentedResponse());

        $response = $this->createClient($httpClient)->getItem($this->documentedRequest());

        $item = $response->item;

        self::assertInstanceOf(AttributeValueMap::class, $item);
        self::assertSame(['Tags', 'LastPostDateTime', 'Message'], $item->keys());
        self::assertSame('201303190436', $item->get('LastPostDateTime')?->string);
        self::assertSame(
            "I want to update multiple items in a single call. What's the best way to do that?",
            $item->get('Message')?->string,
        );

        $tags = $item->get('Tags')?->stringSet;

        self::assertInstanceOf(StringSet::class, $tags);
        self::assertSame(['Update', 'Multiple Items', 'HelpMe'], $tags->toArray());
    }

    /**
     * @throws ExceptionInterface
     * @throws InvalidArgumentException
     * @throws MissingCredentialsException
     * @throws NotFoundException
     */
    public function testReturnsTheConsumedCapacityWhenTheServiceReportsIt(): void
    {
        $httpClient = new MockClient();
        $httpClient->addResponse($this->documentedResponse());

        $response = $this->createClient($httpClient)->getItem($this->documentedRequest());

        $consumedCapacity = $response->consumedCapacity;

        self::assertInstanceOf(ConsumedCapacity::class, $consumedCapacity);
        self::assertSame(1.0, $consumedCapacity->capacityUnits);
        self::assertSame('Thread', $consumedCapacity->tableName);
    }

    /**
     * @throws ExceptionInterface
     * @throws InvalidArgumentException
     * @throws MissingCredentialsException
     * @throws NotFoundException
     */
    public function testLeavesTheConsumedCapacityEmptyWhenTheServiceOmitsIt(): void
    {
        $httpClient = new MockClient();
        $httpClient->addResponse(new Response(body: '{"Item":{"Subject":{"S":"How do I update multiple items?"}}}'));

        $response = $this->createClient($httpClient)->getItem($this->documentedRequest());

        self::assertNull($response->consumedCapacity);
        self::assertSame('How do I update multiple items?', $response->item?->get('Subject')?->string);
    }

    /**
     * @throws ExceptionInterface
     * @throws InvalidArgumentException
     * @throws MissingCredentialsException
     * @throws NotFoundException
     */
    public function testWrapsTheFailureOfTheUnderlyingHttpClient(): void
    {
        $transportFailure = new class ('Connection timed out') extends RuntimeException implements
            ClientExceptionInterface {};
        $httpClient = new MockClient();
        $httpClient->addException($transportFailure);

        try {
            $this->createClient($httpClient)->getItem($this->documentedRequest());
            self::fail('Expected a ' . HttpClientException::class . '.');
        } catch (HttpClientException $exception) {
            self::assertSame('Connection timed out', $exception->getMessage());
            self::assertSame($transportFailure, $exception->getPrevious());
        }
    }

    /**
     * The service answers an error with a payload that carries no Item, so there is nothing to deserialize.
     *
     * @throws ExceptionInterface
     * @throws InvalidArgumentException
     * @throws MissingCredentialsException
     * @throws NotFoundException
     */
    public function testWrapsAResponseTheServiceRejected(): void
    {
        $httpClient = new MockClient();
        $httpClient->addResponse(new Response(
            400,
            body: '{"__type":"com.amazonaws.dynamodb.v20120810#ResourceNotFoundException",'
            . '"message":"Requested resource not found"}',
        ));

        try {
            $this->createClient($httpClient)->getItem($this->documentedRequest());
            self::fail('Expected a ' . BadResponseException::class . '.');
        } catch (BadResponseException $exception) {
            self::assertSame(400, $exception->response->getStatusCode());
        }
    }

    /**
     * The service answers a key that matches nothing with a payload that carries no Item.
     *
     * @throws ExceptionInterface
     * @throws InvalidArgumentException
     * @throws MissingCredentialsException
     * @throws NotFoundException
     */
    public function testLeavesTheItemEmptyWhenTheKeyMatchesNothing(): void
    {
        $httpClient = new MockClient();
        $httpClient->addResponse(new Response(
            body: '{"ConsumedCapacity":{"CapacityUnits":1,"TableName":"Thread"}}',
        ));

        $response = $this->createClient($httpClient)->getItem($this->documentedRequest());

        self::assertNull($response->item);
        self::assertSame(1.0, $response->consumedCapacity?->capacityUnits);
    }

    /**
     * An Item that is a JSON array cannot become an {@see AttributeValueMap}.
     *
     * @throws ExceptionInterface
     * @throws InvalidArgumentException
     * @throws MissingCredentialsException
     * @throws NotFoundException
     */
    public function testWrapsAResponseItCannotDeserialize(): void
    {
        $httpClient = new MockClient();
        $httpClient->addResponse(new Response(
            body: '{"Item":[{"S":"How do I update multiple items?"}]}',
        ));

        try {
            $this->createClient($httpClient)->getItem($this->documentedRequest());
            self::fail('Expected a ' . ResponseDeserializationException::class . '.');
        } catch (ResponseDeserializationException $exception) {
            self::assertSame(200, $exception->response->getStatusCode());
        }
    }

    /**
     * @throws ExceptionInterface
     * @throws InvalidArgumentException
     * @throws MissingCredentialsException
     * @throws NotFoundException
     */
    public function testWrapsAFailureToSerializeTheRequest(): void
    {
        $serializer = self::createStub(SerializerInterface::class);
        $serializer->method('serialize')->willThrowException(new RuntimeException('Broken serializer'));

        $request = $this->documentedRequest();

        try {
            $this->createClient(new MockClient(), serializer: $serializer)->getItem($request);
            self::fail('Expected a ' . RequestSerializationException::class . '.');
        } catch (RequestSerializationException $exception) {
            self::assertSame($request, $exception->payload);
        }
    }

    /**
     * @throws ExceptionInterface
     * @throws InvalidArgumentException
     * @throws MissingCredentialsException
     * @throws NotFoundException
     */
    public function testWrapsInvalidArgumentsIntoItsOwnException(): void
    {
        $requestFactory = self::createStub(RequestFactoryInterface::class);
        $requestFactory->method('createRequest')->willThrowException(
            new InvalidArgumentException('Unsupported method'),
        );

        try {
            $this->createClient(new MockClient(), requestFactory: $requestFactory)
                ->getItem($this->documentedRequest())
            ;
            self::fail('Expected a ' . ClientInvalidArgumentException::class . '.');
        } catch (ClientInvalidArgumentException $exception) {
            self::assertSame('Unsupported method', $exception->getMessage());
        }
    }

    /**
     * @throws InvalidArgumentException
     * @throws MissingCredentialsException
     * @throws NotFoundException
     */
    private function createClient(
        MockClient $httpClient,
        ?RequestFactoryInterface $requestFactory = null,
        ?SerializerInterface $serializer = null,
    ): DynamoDBClient {
        return new DynamoDBClient(
            'eu-central-1',
            new Credentials('AKIDEXAMPLE', 'secret'),
            $httpClient,
            $requestFactory,
            serializer: $serializer,
        );
    }

    /**
     * @throws InvalidArgumentException
     */
    private function documentedRequest(): GetItemRequest
    {
        return new GetItemRequest(
            key: new AttributeValueMap([
                'ForumName' => new AttributeValue(string: 'Amazon DynamoDB'),
                'Subject' => new AttributeValue(string: 'How do I update multiple items?'),
            ]),
            tableName: 'Thread',
            consistentRead: true,
            projectionExpression: 'LastPostDateTime, Message, Tags',
            returnConsumedCapacity: ReturnConsumedCapacity::TOTAL,
        );
    }

    private function documentedResponse(): Response
    {
        $body = file_get_contents(self::RESPONSE_FIXTURE);

        self::assertIsString($body);

        return new Response(body: $body);
    }
}
