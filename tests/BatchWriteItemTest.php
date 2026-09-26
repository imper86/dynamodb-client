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
use Imper86\DynamoDBClient\Message\BatchWriteItemRequest;
use Imper86\DynamoDBClient\Model\AttributeValue;
use Imper86\DynamoDBClient\Model\AttributeValueMap;
use Imper86\DynamoDBClient\Model\ConsumedCapacityList;
use Imper86\DynamoDBClient\Model\Credentials;
use Imper86\DynamoDBClient\Model\DeleteRequest;
use Imper86\DynamoDBClient\Model\ItemCollectionMetricsList;
use Imper86\DynamoDBClient\Model\ItemCollectionMetricsListMap;
use Imper86\DynamoDBClient\Model\PutRequest;
use Imper86\DynamoDBClient\Model\ReturnConsumedCapacity;
use Imper86\DynamoDBClient\Model\ReturnItemCollectionMetrics;
use Imper86\DynamoDBClient\Model\WriteRequest;
use Imper86\DynamoDBClient\Model\WriteRequestList;
use Imper86\DynamoDBClient\Model\WriteRequestListMap;
use Imper86\DynamoDBClient\ValueObject\DoubleList;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

use function file_get_contents;
use function range;

/**
 * The messages exchanged here are the "Multiple Operations on One Table" example of the
 * BatchWriteItem reference.
 *
 * @see https://docs.aws.amazon.com/amazondynamodb/latest/APIReference/API_BatchWriteItem.html
 * @internal
 */
#[CoversClass(DynamoDBClient::class)]
final class BatchWriteItemTest extends TestCase
{
    private const REQUEST_FIXTURE = __DIR__ . '/fixtures/batch-write-item-request.json';

    private const RESPONSE_FIXTURE = __DIR__ . '/fixtures/batch-write-item-response.json';

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

        $this->createClient($httpClient)->batchWriteItem($this->documentedRequest());

        $sent = $httpClient->getLastRequest();

        self::assertInstanceOf(Request::class, $sent);
        self::assertSame('POST', $sent->getMethod());
        self::assertSame('https://dynamodb.eu-central-1.amazonaws.com/', $sent->getUri()->__toString());
        self::assertSame('DynamoDB_20120810.BatchWriteItem', $sent->getHeaderLine('X-Amz-Target'));
        self::assertJsonStringEqualsJsonFile(self::REQUEST_FIXTURE, $sent->getBody()->__toString());
    }

    /**
     * @throws ExceptionInterface
     * @throws InvalidArgumentException
     * @throws MissingCredentialsException
     * @throws NotFoundException
     */
    public function testSendsADeleteWithoutAPutAlongsideIt(): void
    {
        $httpClient = new MockClient();
        $httpClient->addResponse(new Response(body: '{"UnprocessedItems":{}}'));

        $this->createClient($httpClient)->batchWriteItem(new BatchWriteItemRequest(
            requestItems: new WriteRequestListMap([
                'Forum' => new WriteRequestList([
                    new WriteRequest(deleteRequest: new DeleteRequest(
                        new AttributeValueMap(['Name' => new AttributeValue(string: 'Amazon RDS')]),
                    )),
                ]),
            ]),
            returnItemCollectionMetrics: ReturnItemCollectionMetrics::SIZE,
        ));

        $sent = $httpClient->getLastRequest();

        self::assertInstanceOf(Request::class, $sent);
        self::assertJsonStringEqualsJsonString(
            '{"RequestItems":{"Forum":[{"DeleteRequest":{"Key":{"Name":{"S":"Amazon RDS"}}}}]},'
            . '"ReturnItemCollectionMetrics":"SIZE"}',
            $sent->getBody()->__toString(),
        );
    }

    /**
     * The unprocessed items come back in the shape of RequestItems, ready to be retried as they are.
     *
     * @throws ExceptionInterface
     * @throws InvalidArgumentException
     * @throws MissingCredentialsException
     * @throws NotFoundException
     */
    public function testReturnsTheWritesThatWereLeftUnprocessed(): void
    {
        $httpClient = new MockClient();
        $httpClient->addResponse($this->documentedResponse());

        $response = $this->createClient($httpClient)->batchWriteItem($this->documentedRequest());

        self::assertSame(['Forum'], $response->unprocessedItems->keys());

        $unprocessed = $response->unprocessedItems->get('Forum');

        self::assertInstanceOf(WriteRequestList::class, $unprocessed);
        self::assertCount(1, $unprocessed);
        self::assertNull($unprocessed->get(0)?->deleteRequest);
        self::assertSame('Amazon ElastiCache', $unprocessed->get(0)?->putRequest?->item->get('Name')?->string);

        $retried = new BatchWriteItemRequest($response->unprocessedItems);

        self::assertSame($unprocessed, $retried->requestItems->get('Forum'));
    }

    /**
     * @throws ExceptionInterface
     * @throws InvalidArgumentException
     * @throws MissingCredentialsException
     * @throws NotFoundException
     */
    public function testReturnsTheConsumedCapacity(): void
    {
        $httpClient = new MockClient();
        $httpClient->addResponse($this->documentedResponse());

        $response = $this->createClient($httpClient)->batchWriteItem($this->documentedRequest());

        $consumedCapacity = $response->consumedCapacity;

        self::assertInstanceOf(ConsumedCapacityList::class, $consumedCapacity);
        self::assertCount(1, $consumedCapacity);
        self::assertSame('Forum', $consumedCapacity->get(0)?->tableName);
        self::assertSame(3.0, $consumedCapacity->get(0)?->capacityUnits);
    }

    /**
     * @throws ExceptionInterface
     * @throws InvalidArgumentException
     * @throws MissingCredentialsException
     * @throws NotFoundException
     */
    public function testLeavesTheItemCollectionMetricsNullWhenNotRequested(): void
    {
        $httpClient = new MockClient();
        $httpClient->addResponse($this->documentedResponse());

        $response = $this->createClient($httpClient)->batchWriteItem($this->documentedRequest());

        self::assertNull($response->itemCollectionMetrics);
    }

    /**
     * @throws ExceptionInterface
     * @throws InvalidArgumentException
     * @throws MissingCredentialsException
     * @throws NotFoundException
     */
    public function testReturnsTheItemCollectionMetricsOfEveryTable(): void
    {
        $httpClient = new MockClient();
        $httpClient->addResponse(new Response(body: '{"UnprocessedItems":{},"ItemCollectionMetrics":{"Thread":[{'
            . '"ItemCollectionKey":{"ForumName":{"S":"Amazon DynamoDB"}},'
            . '"SizeEstimateRangeGB":[0.5, 1]}]}}'));

        $response = $this->createClient($httpClient)->batchWriteItem($this->documentedRequest());

        $metrics = $response->itemCollectionMetrics;

        self::assertInstanceOf(ItemCollectionMetricsListMap::class, $metrics);

        $thread = $metrics->get('Thread');

        self::assertInstanceOf(ItemCollectionMetricsList::class, $thread);
        self::assertCount(1, $thread);
        self::assertSame('Amazon DynamoDB', $thread->get(0)?->itemCollectionKey?->get('ForumName')?->string);

        $range = $thread->get(0)?->sizeEstimateRangeGB;

        self::assertInstanceOf(DoubleList::class, $range);
        self::assertSame([0.5, 1], $range->toArray());
    }

    /**
     * @throws ExceptionInterface
     * @throws InvalidArgumentException
     * @throws MissingCredentialsException
     * @throws NotFoundException
     */
    public function testLeavesTheUnprocessedItemsEmptyWhenEveryWriteWasProcessed(): void
    {
        $httpClient = new MockClient();
        $httpClient->addResponse(new Response(body: '{"UnprocessedItems":{}}'));

        $response = $this->createClient($httpClient)->batchWriteItem($this->documentedRequest());

        self::assertTrue($response->unprocessedItems->isEmpty());
        self::assertNull($response->consumedCapacity);
    }

    /**
     * @throws ExceptionInterface
     * @throws InvalidArgumentException
     * @throws MissingCredentialsException
     * @throws NotFoundException
     */
    public function testLeavesTheUnprocessedItemsEmptyWhenTheServiceOmitsThem(): void
    {
        $httpClient = new MockClient();
        $httpClient->addResponse(new Response(body: '{"ConsumedCapacity":[{"TableName":"Forum","CapacityUnits":4}]}'));

        $response = $this->createClient($httpClient)->batchWriteItem($this->documentedRequest());

        self::assertTrue($response->unprocessedItems->isEmpty());
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
        $httpClient->addResponse(new Response(body: '{"UnprocessedItems":{"Forum":[{}]}}'));

        try {
            $this->createClient($httpClient)->batchWriteItem($this->documentedRequest());
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

        new BatchWriteItemRequest(new WriteRequestListMap());
    }

    /**
     * @throws InvalidArgumentException
     */
    public function testRejectsMoreTablesThanTheServiceAccepts(): void
    {
        $tables = [];

        foreach (range(1, 26) as $number) {
            $tables["Forum{$number}"] = new WriteRequestList([$this->put("Forum {$number}")]);
        }

        $this->expectException(InvalidArgumentException::class);

        new BatchWriteItemRequest(new WriteRequestListMap($tables));
    }

    /**
     * @throws InvalidArgumentException
     */
    public function testRejectsATableWithoutWriteRequests(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new BatchWriteItemRequest(new WriteRequestListMap(['Forum' => new WriteRequestList()]));
    }

    /**
     * The limit of 25 counts the write requests of every table together.
     *
     * @throws InvalidArgumentException
     */
    public function testRejectsMoreWriteRequestsThanTheServiceAccepts(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new BatchWriteItemRequest(new WriteRequestListMap([
            'Forum' => new WriteRequestList($this->puts(13)),
            'Thread' => new WriteRequestList($this->puts(13)),
        ]));
    }

    /**
     * @throws InvalidArgumentException
     */
    public function testAcceptsExactlyAsManyWriteRequestsAsTheServiceAllows(): void
    {
        $request = new BatchWriteItemRequest(new WriteRequestListMap([
            'Forum' => new WriteRequestList($this->puts(12)),
            'Thread' => new WriteRequestList($this->puts(13)),
        ]));

        self::assertCount(2, $request->requestItems);
    }

    /**
     * @throws InvalidArgumentException
     */
    public function testRejectsAWriteRequestWithoutAnOperation(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new WriteRequest();
    }

    /**
     * @throws InvalidArgumentException
     */
    public function testRejectsAWriteRequestWithBothOperations(): void
    {
        $key = new AttributeValueMap(['Name' => new AttributeValue(string: 'Amazon RDS')]);

        $this->expectException(InvalidArgumentException::class);

        new WriteRequest(deleteRequest: new DeleteRequest($key), putRequest: new PutRequest($key));
    }

    /**
     * @throws ExceptionInterface
     * @throws InvalidArgumentException
     * @throws MissingCredentialsException
     * @throws NotFoundException
     */
    public function testRejectsASizeEstimateThatIsNotANumber(): void
    {
        $httpClient = new MockClient();
        $httpClient->addResponse(new Response(body: '{"ItemCollectionMetrics":{"Thread":[{'
            . '"SizeEstimateRangeGB":["0.5","1"]}]}}'));

        $this->expectException(ResponseDeserializationException::class);

        $this->createClient($httpClient)->batchWriteItem($this->documentedRequest());
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
    private function documentedRequest(): BatchWriteItemRequest
    {
        return new BatchWriteItemRequest(
            requestItems: new WriteRequestListMap([
                'Forum' => new WriteRequestList([
                    $this->put('Amazon DynamoDB'),
                    $this->put('Amazon RDS'),
                    $this->put('Amazon Redshift'),
                    $this->put('Amazon ElastiCache'),
                ]),
            ]),
            returnConsumedCapacity: ReturnConsumedCapacity::TOTAL,
        );
    }

    /**
     * @throws InvalidArgumentException
     */
    private function put(string $name): WriteRequest
    {
        return new WriteRequest(putRequest: new PutRequest(new AttributeValueMap([
            'Name' => new AttributeValue(string: $name),
            'Category' => new AttributeValue(string: 'Amazon Web Services'),
        ])));
    }

    /**
     * @return list<WriteRequest>
     * @throws InvalidArgumentException
     */
    private function puts(int $count): array
    {
        $puts = [];

        foreach (range(1, $count) as $number) {
            $puts[] = $this->put("Service {$number}");
        }

        return $puts;
    }

    private function documentedResponse(): Response
    {
        $body = file_get_contents(self::RESPONSE_FIXTURE);

        self::assertIsString($body);

        return new Response(body: $body);
    }
}
