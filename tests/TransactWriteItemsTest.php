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
use Imper86\DynamoDBClient\Message\TransactWriteItemsRequest;
use Imper86\DynamoDBClient\Model\AttributeValue;
use Imper86\DynamoDBClient\Model\AttributeValueMap;
use Imper86\DynamoDBClient\Model\ConditionCheck;
use Imper86\DynamoDBClient\Model\ConsumedCapacityList;
use Imper86\DynamoDBClient\Model\Credentials;
use Imper86\DynamoDBClient\Model\Delete;
use Imper86\DynamoDBClient\Model\ItemCollectionMetricsListMap;
use Imper86\DynamoDBClient\Model\Put;
use Imper86\DynamoDBClient\Model\ReturnConsumedCapacity;
use Imper86\DynamoDBClient\Model\ReturnItemCollectionMetrics;
use Imper86\DynamoDBClient\Model\ReturnValuesOnConditionCheckFailure;
use Imper86\DynamoDBClient\Model\TransactWriteItem;
use Imper86\DynamoDBClient\Model\TransactWriteItemList;
use Imper86\DynamoDBClient\Model\Update;
use Imper86\DynamoDBClient\ValueObject\DoubleList;
use Imper86\DynamoDBClient\ValueObject\NonEmptyStringMap;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

use function file_get_contents;
use function str_repeat;

/**
 * The TransactWriteItems reference has no Examples section, so the fixtures are built from its request and
 * response syntax: one transaction that checks an artist exists in an `Artists` table, then puts, updates
 * and deletes songs in a `Music` table, asking for consumed capacity and item collection metrics.
 *
 * @see https://docs.aws.amazon.com/amazondynamodb/latest/APIReference/API_TransactWriteItems.html
 * @internal
 */
#[CoversClass(DynamoDBClient::class)]
final class TransactWriteItemsTest extends TestCase
{
    private const string REQUEST_FIXTURE = __DIR__ . '/fixtures/transact-write-items-request.json';

    private const string RESPONSE_FIXTURE = __DIR__ . '/fixtures/transact-write-items-response.json';

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

        $this->createClient($httpClient)->transactWriteItems($this->documentedRequest());

        $sent = $httpClient->getLastRequest();

        self::assertInstanceOf(Request::class, $sent);
        self::assertSame('DynamoDB_20120810.TransactWriteItems', $sent->getHeaderLine('X-Amz-Target'));
        self::assertJsonStringEqualsJsonFile(self::REQUEST_FIXTURE, $sent->getBody()->__toString());
    }

    /**
     * @throws ExceptionInterface
     * @throws InvalidArgumentException
     * @throws MissingCredentialsException
     * @throws NotFoundException
     */
    public function testReturnsTheConsumedCapacityInTheOrderOfTheActions(): void
    {
        $httpClient = new MockClient();
        $httpClient->addResponse($this->documentedResponse());

        $response = $this->createClient($httpClient)->transactWriteItems($this->documentedRequest());

        $consumedCapacity = $response->consumedCapacity;

        self::assertInstanceOf(ConsumedCapacityList::class, $consumedCapacity);
        self::assertCount(2, $consumedCapacity);
        self::assertSame('Artists', $consumedCapacity->get(0)?->tableName);
        self::assertSame(12.0, $consumedCapacity->get(1)?->capacityUnits);
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
        $httpClient->addResponse($this->documentedResponse());

        $response = $this->createClient($httpClient)->transactWriteItems($this->documentedRequest());

        $itemCollectionMetrics = $response->itemCollectionMetrics;

        self::assertInstanceOf(ItemCollectionMetricsListMap::class, $itemCollectionMetrics);
        self::assertSame(['Music'], $itemCollectionMetrics->keys());

        $metrics = $itemCollectionMetrics->get('Music')?->get(0);
        $sizeEstimateRange = $metrics?->sizeEstimateRangeGB;

        self::assertSame('Acme Band', $metrics?->itemCollectionKey?->get('Artist')?->string);
        self::assertInstanceOf(DoubleList::class, $sizeEstimateRange);
        self::assertSame([0, 1], $sizeEstimateRange->toArray());
    }

    /**
     * A transaction that asked for neither statistic answers with an empty body.
     *
     * @throws ExceptionInterface
     * @throws InvalidArgumentException
     * @throws MissingCredentialsException
     * @throws NotFoundException
     */
    public function testLeavesTheElementsTheServiceOmitsNull(): void
    {
        $httpClient = new MockClient();
        $httpClient->addResponse(new Response(body: '{}'));

        $response = $this->createClient($httpClient)->transactWriteItems($this->documentedRequest());

        self::assertNull($response->itemCollectionMetrics);
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
        $httpClient->addResponse(new Response(body: '{"ItemCollectionMetrics":{"Music":"x"}}'));

        $this->expectException(ResponseDeserializationException::class);

        $this->createClient($httpClient)->transactWriteItems($this->documentedRequest());
    }

    /**
     * @throws InvalidArgumentException
     */
    public function testRejectsAnEmptyTransaction(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new TransactWriteItemsRequest(new TransactWriteItemList());
    }

    /**
     * @throws InvalidArgumentException
     */
    public function testRejectsATransactionLongerThanTheServiceAccepts(): void
    {
        $items = [];

        for ($i = 0; $i < 101; ++$i) {
            $items[] = TransactWriteItem::delete($this->key('Old Song'), 'Music');
        }

        $this->expectException(InvalidArgumentException::class);

        new TransactWriteItemsRequest(new TransactWriteItemList($items));
    }

    /**
     * @throws InvalidArgumentException
     */
    public function testRejectsAClientRequestTokenLongerThanTheServiceAccepts(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new TransactWriteItemsRequest(
            new TransactWriteItemList([TransactWriteItem::delete($this->key('Old Song'), 'Music')]),
            clientRequestToken: str_repeat('a', 37),
        );
    }

    /**
     * @throws InvalidArgumentException
     */
    public function testRejectsAConditionCheckOnATableNameLongerThanTheServiceAccepts(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new ConditionCheck('attribute_exists(Artist)', $this->key('Old Song'), str_repeat('a', 1025));
    }

    /**
     * @throws InvalidArgumentException
     */
    public function testRejectsADeleteOnATableNameLongerThanTheServiceAccepts(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new Delete($this->key('Old Song'), str_repeat('a', 1025));
    }

    /**
     * @throws InvalidArgumentException
     */
    public function testRejectsAPutOnATableNameLongerThanTheServiceAccepts(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new Put($this->key('Old Song'), str_repeat('a', 1025));
    }

    /**
     * @throws InvalidArgumentException
     */
    public function testRejectsAnUpdateOnATableNameLongerThanTheServiceAccepts(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new Update($this->key('Old Song'), str_repeat('a', 1025), 'REMOVE Plays');
    }

    /**
     * @throws InvalidArgumentException
     */
    private function documentedRequest(): TransactWriteItemsRequest
    {
        return new TransactWriteItemsRequest(
            transactItems: new TransactWriteItemList([
                TransactWriteItem::conditionCheck(
                    conditionExpression: 'attribute_exists(Artist)',
                    key: new AttributeValueMap(['Artist' => AttributeValue::string('Acme Band')]),
                    tableName: 'Artists',
                    returnValuesOnConditionCheckFailure: ReturnValuesOnConditionCheckFailure::ALL_OLD,
                ),
                TransactWriteItem::put(
                    item: new AttributeValueMap([
                        'Artist' => AttributeValue::string('Acme Band'),
                        'SongTitle' => AttributeValue::string('PartiQL Rocks'),
                        'AlbumTitle' => AttributeValue::string('Songs About Life'),
                    ]),
                    tableName: 'Music',
                    conditionExpression: 'attribute_not_exists(SongTitle)',
                ),
                TransactWriteItem::update(
                    key: $this->key('Happy Day'),
                    tableName: 'Music',
                    updateExpression: 'SET #plays = #plays + :one',
                    expressionAttributeNames: new NonEmptyStringMap(['#plays' => 'Plays']),
                    expressionAttributeValues: new AttributeValueMap([':one' => AttributeValue::number(1)]),
                ),
                TransactWriteItem::delete($this->key('Old Song'), 'Music'),
            ]),
            clientRequestToken: '5f2a4b1c-8d3e-4f60-9a7b-0c1d2e3f4a5b',
            returnConsumedCapacity: ReturnConsumedCapacity::TOTAL,
            returnItemCollectionMetrics: ReturnItemCollectionMetrics::SIZE,
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
