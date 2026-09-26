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
use Imper86\DynamoDBClient\Message\BatchGetItemRequest;
use Imper86\DynamoDBClient\Model\AttributeValue;
use Imper86\DynamoDBClient\Model\AttributeValueMap;
use Imper86\DynamoDBClient\Model\ConsumedCapacityList;
use Imper86\DynamoDBClient\Model\ItemList;
use Imper86\DynamoDBClient\Model\KeyList;
use Imper86\DynamoDBClient\Model\KeysAndAttributes;
use Imper86\DynamoDBClient\Model\KeysAndAttributesMap;
use Imper86\DynamoDBClient\Model\Credentials;
use Imper86\DynamoDBClient\Model\ReturnConsumedCapacity;
use Imper86\DynamoDBClient\ValueObject\NonEmptyStringList;
use Imper86\DynamoDBClient\ValueObject\StringSet;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

use function file_get_contents;
use function range;

/**
 * The messages exchanged here are the "Retrieve Items from Multiple Tables" example of the
 * BatchGetItem reference, whose sample response is missing a comma between the two tables.
 *
 * @see https://docs.aws.amazon.com/amazondynamodb/latest/APIReference/API_BatchGetItem.html
 * @internal
 */
#[CoversClass(DynamoDBClient::class)]
final class BatchGetItemTest extends TestCase
{
    private const REQUEST_FIXTURE = __DIR__ . '/fixtures/batch-get-item-request.json';

    private const RESPONSE_FIXTURE = __DIR__ . '/fixtures/batch-get-item-response.json';

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

        $this->createClient($httpClient)->batchGetItem($this->documentedRequest());

        $sent = $httpClient->getLastRequest();

        self::assertInstanceOf(Request::class, $sent);
        self::assertSame('POST', $sent->getMethod());
        self::assertSame('https://dynamodb.eu-central-1.amazonaws.com/', $sent->getUri()->__toString());
        self::assertSame('DynamoDB_20120810.BatchGetItem', $sent->getHeaderLine('X-Amz-Target'));
        self::assertJsonStringEqualsJsonFile(self::REQUEST_FIXTURE, $sent->getBody()->__toString());
    }

    /**
     * @throws ExceptionInterface
     * @throws InvalidArgumentException
     * @throws MissingCredentialsException
     * @throws NotFoundException
     */
    public function testReturnsTheItemsOfEveryTableThatAnswered(): void
    {
        $httpClient = new MockClient();
        $httpClient->addResponse($this->documentedResponse());

        $response = $this->createClient($httpClient)->batchGetItem($this->documentedRequest());

        self::assertSame(['Forum', 'Thread'], $response->responses->keys());

        $forum = $response->responses->get('Forum');

        self::assertInstanceOf(ItemList::class, $forum);
        self::assertCount(3, $forum);
        self::assertSame('Amazon DynamoDB', $forum->get(0)?->get('Name')?->string);
        self::assertSame('5', $forum->get(0)?->get('Threads')?->number);
        self::assertSame('Amazon Redshift', $forum->get(2)?->get('Name')?->string);
    }

    /**
     * @throws ExceptionInterface
     * @throws InvalidArgumentException
     * @throws MissingCredentialsException
     * @throws NotFoundException
     */
    public function testReturnsTheSetsOfAnItemAsValueObjects(): void
    {
        $httpClient = new MockClient();
        $httpClient->addResponse($this->documentedResponse());

        $response = $this->createClient($httpClient)->batchGetItem($this->documentedRequest());

        $tags = $response->responses->get('Thread')?->get(0)?->get('Tags')?->stringSet;

        self::assertInstanceOf(StringSet::class, $tags);
        self::assertSame(['Reads', 'MultipleUsers'], $tags->toArray());
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

        $response = $this->createClient($httpClient)->batchGetItem($this->documentedRequest());

        $consumedCapacity = $response->consumedCapacity;

        self::assertInstanceOf(ConsumedCapacityList::class, $consumedCapacity);
        self::assertCount(2, $consumedCapacity);
        self::assertSame('Forum', $consumedCapacity->get(0)?->tableName);
        self::assertSame(3.0, $consumedCapacity->get(0)?->capacityUnits);
        self::assertSame(1.0, $consumedCapacity->get(1)?->capacityUnits);
    }

    /**
     * A fully processed batch answers with an empty map rather than omitting the element.
     *
     * @throws ExceptionInterface
     * @throws InvalidArgumentException
     * @throws MissingCredentialsException
     * @throws NotFoundException
     */
    public function testLeavesTheUnprocessedKeysEmptyWhenEveryKeyWasRead(): void
    {
        $httpClient = new MockClient();
        $httpClient->addResponse($this->documentedResponse());

        $response = $this->createClient($httpClient)->batchGetItem($this->documentedRequest());

        self::assertTrue($response->unprocessedKeys->isEmpty());
    }

    /**
     * The unprocessed keys come back in the shape of RequestItems, ready to be retried as they are.
     *
     * @throws ExceptionInterface
     * @throws InvalidArgumentException
     * @throws MissingCredentialsException
     * @throws NotFoundException
     */
    public function testReturnsTheKeysAPartialResultLeftUnread(): void
    {
        $httpClient = new MockClient();
        $httpClient->addResponse(new Response(body: '{"Responses":{},"UnprocessedKeys":{"Forum":{'
            . '"Keys":[{"Name":{"S":"Amazon Redshift"}}],'
            . '"ProjectionExpression":"Name, Threads"}}}'));

        $response = $this->createClient($httpClient)->batchGetItem($this->documentedRequest());

        $unread = $response->unprocessedKeys->get('Forum');

        self::assertInstanceOf(KeysAndAttributes::class, $unread);
        self::assertSame('Name, Threads', $unread->projectionExpression);
        self::assertCount(1, $unread->keys);
        self::assertSame('Amazon Redshift', $unread->keys->get(0)?->get('Name')?->string);

        $retried = new BatchGetItemRequest(new KeysAndAttributesMap(['Forum' => $unread]));

        self::assertSame($unread, $retried->requestItems->get('Forum'));
    }

    /**
     * @throws ExceptionInterface
     * @throws InvalidArgumentException
     * @throws MissingCredentialsException
     * @throws NotFoundException
     */
    public function testLeavesBothMapsEmptyWhenTheServiceOmitsThem(): void
    {
        $httpClient = new MockClient();
        $httpClient->addResponse(new Response(body: '{"ConsumedCapacity":[{"TableName":"Forum","CapacityUnits":3}]}'));

        $response = $this->createClient($httpClient)->batchGetItem($this->documentedRequest());

        self::assertTrue($response->responses->isEmpty());
        self::assertTrue($response->unprocessedKeys->isEmpty());
        self::assertCount(1, $response->consumedCapacity ?? []);
    }

    /**
     * @throws ExceptionInterface
     * @throws InvalidArgumentException
     * @throws MissingCredentialsException
     * @throws NotFoundException
     */
    public function testWrapsAResponseItCannotDeserialize(): void
    {
        $httpClient = new MockClient();
        $httpClient->addResponse(new Response(body: '{"Responses":{"Forum":{"Name":{"S":"Amazon DynamoDB"}}}}'));

        try {
            $this->createClient($httpClient)->batchGetItem($this->documentedRequest());
            self::fail('Expected a ' . ResponseDeserializationException::class . '.');
        } catch (ResponseDeserializationException $exception) {
            self::assertSame(200, $exception->response->getStatusCode());
        }
    }

    /**
     * @throws InvalidArgumentException
     */
    public function testRejectsARequestWithoutTables(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new BatchGetItemRequest(new KeysAndAttributesMap());
    }

    /**
     * @throws InvalidArgumentException
     */
    public function testRejectsMoreTablesThanTheServiceAccepts(): void
    {
        $tables = [];

        foreach (range(1, 101) as $number) {
            $tables["Forum{$number}"] = new KeysAndAttributes($this->forumKeys());
        }

        $this->expectException(InvalidArgumentException::class);

        new BatchGetItemRequest(new KeysAndAttributesMap($tables));
    }

    /**
     * @throws InvalidArgumentException
     */
    public function testRejectsATableWithoutKeys(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new KeysAndAttributes(new KeyList());
    }

    /**
     * @throws InvalidArgumentException
     */
    public function testRejectsMoreKeysThanTheServiceAccepts(): void
    {
        $keys = [];

        foreach (range(1, 101) as $number) {
            $keys[] = new AttributeValueMap(['Name' => new AttributeValue(string: "Forum {$number}")]);
        }

        $this->expectException(InvalidArgumentException::class);

        new KeysAndAttributes(new KeyList($keys));
    }

    /**
     * @throws InvalidArgumentException
     */
    public function testRejectsAnEmptyAttributesToGetList(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new KeysAndAttributes($this->forumKeys(), attributesToGet: new NonEmptyStringList());
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
    private function documentedRequest(): BatchGetItemRequest
    {
        return new BatchGetItemRequest(
            requestItems: new KeysAndAttributesMap([
                'Forum' => new KeysAndAttributes(
                    keys: $this->forumKeys(),
                    projectionExpression: 'Name, Threads, Messages, Views',
                ),
                'Thread' => new KeysAndAttributes(
                    keys: new KeyList([
                        new AttributeValueMap([
                            'ForumName' => new AttributeValue(string: 'Amazon DynamoDB'),
                            'Subject' => new AttributeValue(string: 'Concurrent reads'),
                        ]),
                    ]),
                    consistentRead: true,
                    projectionExpression: 'Tags, Message',
                ),
            ]),
            returnConsumedCapacity: ReturnConsumedCapacity::TOTAL,
        );
    }

    /**
     * @throws InvalidArgumentException
     */
    private function forumKeys(): KeyList
    {
        return new KeyList([
            new AttributeValueMap(['Name' => new AttributeValue(string: 'Amazon DynamoDB')]),
            new AttributeValueMap(['Name' => new AttributeValue(string: 'Amazon RDS')]),
            new AttributeValueMap(['Name' => new AttributeValue(string: 'Amazon Redshift')]),
        ]);
    }

    private function documentedResponse(): Response
    {
        $body = file_get_contents(self::RESPONSE_FIXTURE);

        self::assertIsString($body);

        return new Response(body: $body);
    }
}
