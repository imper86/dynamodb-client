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
use Imper86\DynamoDBClient\Message\TransactGetItemsRequest;
use Imper86\DynamoDBClient\Model\AttributeValue;
use Imper86\DynamoDBClient\Model\AttributeValueMap;
use Imper86\DynamoDBClient\Model\ConsumedCapacityList;
use Imper86\DynamoDBClient\Model\Credentials;
use Imper86\DynamoDBClient\Model\Get;
use Imper86\DynamoDBClient\Model\ItemResponse;
use Imper86\DynamoDBClient\Model\ReturnConsumedCapacity;
use Imper86\DynamoDBClient\Model\TransactGetItem;
use Imper86\DynamoDBClient\Model\TransactGetItemList;
use Imper86\DynamoDBClient\ValueObject\NonEmptyStringMap;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

use function file_get_contents;
use function str_repeat;

/**
 * The TransactGetItems reference has no Examples section, so the fixtures are built from its request and
 * response syntax: two songs read from a `Music` table, the first with a projection, the second one that
 * does not exist.
 *
 * @see https://docs.aws.amazon.com/amazondynamodb/latest/APIReference/API_TransactGetItems.html
 * @internal
 */
#[CoversClass(DynamoDBClient::class)]
final class TransactGetItemsTest extends TestCase
{
    private const string REQUEST_FIXTURE = __DIR__ . '/fixtures/transact-get-items-request.json';

    private const string RESPONSE_FIXTURE = __DIR__ . '/fixtures/transact-get-items-response.json';

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

        $this->createClient($httpClient)->transactGetItems($this->documentedRequest());

        $sent = $httpClient->getLastRequest();

        self::assertInstanceOf(Request::class, $sent);
        self::assertSame('DynamoDB_20120810.TransactGetItems', $sent->getHeaderLine('X-Amz-Target'));
        self::assertJsonStringEqualsJsonFile(self::REQUEST_FIXTURE, $sent->getBody()->__toString());
    }

    /**
     * @throws ExceptionInterface
     * @throws InvalidArgumentException
     * @throws MissingCredentialsException
     * @throws NotFoundException
     */
    public function testReturnsTheResponsesInTheOrderOfTheRequestedItems(): void
    {
        $httpClient = new MockClient();
        $httpClient->addResponse($this->documentedResponse());

        $response = $this->createClient($httpClient)->transactGetItems($this->documentedRequest());

        self::assertCount(2, $response->responses);

        $found = $response->responses->get(0);

        self::assertInstanceOf(ItemResponse::class, $found);

        $item = $found->item;

        self::assertInstanceOf(AttributeValueMap::class, $item);
        self::assertSame(['SongTitle', 'AlbumTitle'], $item->keys());
        self::assertSame('Songs About Life', $item->get('AlbumTitle')?->string);
    }

    /**
     * @throws ExceptionInterface
     * @throws InvalidArgumentException
     * @throws MissingCredentialsException
     * @throws NotFoundException
     */
    public function testLeavesTheItemNullForAnItemThatCouldNotBeRetrieved(): void
    {
        $httpClient = new MockClient();
        $httpClient->addResponse($this->documentedResponse());

        $response = $this->createClient($httpClient)->transactGetItems($this->documentedRequest());

        $missing = $response->responses->get(1);

        self::assertInstanceOf(ItemResponse::class, $missing);
        self::assertNull($missing->item);
    }

    /**
     * The reference says a response for an item that could not be retrieved "is Null", which could also
     * mean a literal `null` in the list rather than an `ItemResponse` without an item.
     *
     * @throws ExceptionInterface
     * @throws InvalidArgumentException
     * @throws MissingCredentialsException
     * @throws NotFoundException
     */
    public function testReadsANullResponseAsAnItemResponseWithoutAnItem(): void
    {
        $httpClient = new MockClient();
        $httpClient->addResponse(new Response(body: '{"Responses":[null]}'));

        $response = $this->createClient($httpClient)->transactGetItems($this->documentedRequest());

        $missing = $response->responses->get(0);

        self::assertInstanceOf(ItemResponse::class, $missing);
        self::assertNull($missing->item);
    }

    /**
     * @throws ExceptionInterface
     * @throws InvalidArgumentException
     * @throws MissingCredentialsException
     * @throws NotFoundException
     */
    public function testReturnsTheConsumedCapacityOfEveryTable(): void
    {
        $httpClient = new MockClient();
        $httpClient->addResponse($this->documentedResponse());

        $response = $this->createClient($httpClient)->transactGetItems($this->documentedRequest());

        $consumedCapacity = $response->consumedCapacity;

        self::assertInstanceOf(ConsumedCapacityList::class, $consumedCapacity);
        self::assertCount(1, $consumedCapacity);
        self::assertSame(4.0, $consumedCapacity->get(0)?->readCapacityUnits);
        self::assertSame('Music', $consumedCapacity->get(0)?->tableName);
    }

    /**
     * @throws ExceptionInterface
     * @throws InvalidArgumentException
     * @throws MissingCredentialsException
     * @throws NotFoundException
     */
    public function testLeavesTheElementsTheServiceOmitsEmpty(): void
    {
        $httpClient = new MockClient();
        $httpClient->addResponse(new Response(body: '{}'));

        $response = $this->createClient($httpClient)->transactGetItems($this->documentedRequest());

        self::assertTrue($response->responses->isEmpty());
        self::assertNull($response->consumedCapacity);
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
        $httpClient->addResponse(new Response(body: '{"Responses":"x"}'));

        $this->expectException(ResponseDeserializationException::class);

        $this->createClient($httpClient)->transactGetItems($this->documentedRequest());
    }

    /**
     * @throws InvalidArgumentException
     */
    public function testRejectsAnEmptyTransaction(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new TransactGetItemsRequest(new TransactGetItemList());
    }

    /**
     * @throws InvalidArgumentException
     */
    public function testRejectsATransactionLongerThanTheServiceAccepts(): void
    {
        $items = [];

        for ($i = 0; $i < 101; ++$i) {
            $items[] = TransactGetItem::get($this->key('Happy Day'), 'Music');
        }

        $this->expectException(InvalidArgumentException::class);

        new TransactGetItemsRequest(new TransactGetItemList($items));
    }

    /**
     * @throws InvalidArgumentException
     */
    public function testRejectsConsumedCapacityPerIndex(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new TransactGetItemsRequest(
            new TransactGetItemList([TransactGetItem::get($this->key('Happy Day'), 'Music')]),
            ReturnConsumedCapacity::INDEXES,
        );
    }

    /**
     * @throws InvalidArgumentException
     */
    public function testRejectsATableNameLongerThanTheServiceAccepts(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new Get($this->key('Happy Day'), str_repeat('a', 1025));
    }

    /**
     * @throws InvalidArgumentException
     */
    private function documentedRequest(): TransactGetItemsRequest
    {
        return new TransactGetItemsRequest(
            transactItems: new TransactGetItemList([
                TransactGetItem::get(
                    key: $this->key('Happy Day'),
                    tableName: 'Music',
                    expressionAttributeNames: new NonEmptyStringMap(['#title' => 'SongTitle']),
                    projectionExpression: '#title, AlbumTitle',
                ),
                TransactGetItem::get($this->key('PartiQL Rocks'), 'Music'),
            ]),
            returnConsumedCapacity: ReturnConsumedCapacity::TOTAL,
        );
    }

    /**
     * @throws InvalidArgumentException
     */
    private function key(string $songTitle): AttributeValueMap
    {
        return new AttributeValueMap([
            'Artist' => AttributeValue::string('Acme Band'),
            'SongTitle' => AttributeValue::string($songTitle),
        ]);
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
