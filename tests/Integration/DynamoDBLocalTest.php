<?php

declare(strict_types=1);

namespace Imper86\DynamoDBClientTests\Integration;

use Http\Discovery\Exception\NotFoundException;
use Imper86\DynamoDBClient\DynamoDBClient;
use Imper86\DynamoDBClient\Exception\BadResponseException;
use Imper86\DynamoDBClient\Exception\ExceptionInterface;
use Imper86\DynamoDBClient\Exception\MissingCredentialsException;
use Imper86\DynamoDBClient\Message\BatchGetItemRequest;
use Imper86\DynamoDBClient\Message\BatchWriteItemRequest;
use Imper86\DynamoDBClient\Message\CreateTableRequest;
use Imper86\DynamoDBClient\Message\DeleteTableRequest;
use Imper86\DynamoDBClient\Message\ExecuteStatementRequest;
use Imper86\DynamoDBClient\Message\GetItemRequest;
use Imper86\DynamoDBClient\Message\PutItemRequest;
use Imper86\DynamoDBClient\Message\QueryRequest;
use Imper86\DynamoDBClient\Message\TransactGetItemsRequest;
use Imper86\DynamoDBClient\Message\TransactWriteItemsRequest;
use Imper86\DynamoDBClient\Model\AttributeDefinition;
use Imper86\DynamoDBClient\Model\AttributeDefinitionList;
use Imper86\DynamoDBClient\Model\AttributeValue;
use Imper86\DynamoDBClient\Model\AttributeValueList;
use Imper86\DynamoDBClient\Model\AttributeValueMap;
use Imper86\DynamoDBClient\Model\BillingMode;
use Imper86\DynamoDBClient\Model\Credentials;
use Imper86\DynamoDBClient\Model\KeyList;
use Imper86\DynamoDBClient\Model\KeysAndAttributes;
use Imper86\DynamoDBClient\Model\KeysAndAttributesMap;
use Imper86\DynamoDBClient\Model\KeySchemaElement;
use Imper86\DynamoDBClient\Model\KeySchemaElementList;
use Imper86\DynamoDBClient\Model\KeyType;
use Imper86\DynamoDBClient\Model\ScalarAttributeType;
use Imper86\DynamoDBClient\Model\TransactGetItem;
use Imper86\DynamoDBClient\Model\TransactGetItemList;
use Imper86\DynamoDBClient\Model\TransactWriteItem;
use Imper86\DynamoDBClient\Model\TransactWriteItemList;
use Imper86\DynamoDBClient\Model\WriteRequest;
use Imper86\DynamoDBClient\Model\WriteRequestList;
use Imper86\DynamoDBClient\Model\WriteRequestListMap;
use InvalidArgumentException;
use JsonException;
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\TestCase;

use function array_map;
use function getenv;
use function is_string;
use function json_decode;
use function sort;
use function sprintf;
use function uniqid;

use const JSON_THROW_ON_ERROR;

/**
 * Runs the client against a real DynamoDB Local, so the whole stack is exercised: signing, the
 * custom endpoint and what the service actually puts on the wire.
 *
 * Start it with `docker compose up -d`, then run
 * `DYNAMODB_LOCAL_ENDPOINT=http://localhost:8000 composer integration`.
 *
 * @internal
 */
#[CoversNothing]
final class DynamoDBLocalTest extends TestCase
{
    private const ENDPOINT_ENV_VARIABLE = 'DYNAMODB_LOCAL_ENDPOINT';

    private DynamoDBClient $client;

    /**
     * @var non-empty-string
     */
    private string $tableName;

    /**
     * @throws ExceptionInterface
     * @throws InvalidArgumentException
     * @throws MissingCredentialsException
     * @throws NotFoundException
     */
    protected function setUp(): void
    {
        $endpoint = getenv(self::ENDPOINT_ENV_VARIABLE);

        if (!is_string($endpoint) || '' === $endpoint) {
            self::markTestSkipped(sprintf(
                'Set %s to the url of a DynamoDB Local, such as http://localhost:8000, to run the integration tests.',
                self::ENDPOINT_ENV_VARIABLE,
            ));
        }

        $this->client = new DynamoDBClient(
            region: 'us-east-1',
            credentials: new Credentials('local', 'local'),
            endpoint: $endpoint,
        );
        $tableName = sprintf('Music-%s', uniqid());

        $this->client->createTable(new CreateTableRequest(
            tableName: $tableName,
            attributeDefinitions: new AttributeDefinitionList([
                new AttributeDefinition('Artist', ScalarAttributeType::STRING),
                new AttributeDefinition('SongTitle', ScalarAttributeType::STRING),
            ]),
            billingMode: BillingMode::PAY_PER_REQUEST,
            keySchema: new KeySchemaElementList([
                new KeySchemaElement('Artist', KeyType::HASH),
                new KeySchemaElement('SongTitle', KeyType::RANGE),
            ]),
        ));

        // Set only once the table exists, so tearDown() never deletes a table that was not created.
        $this->tableName = $tableName;
    }

    /**
     * @throws ExceptionInterface
     * @throws InvalidArgumentException
     */
    protected function tearDown(): void
    {
        if (isset($this->tableName)) {
            $this->client->deleteTable(new DeleteTableRequest($this->tableName));
        }
    }

    /**
     * @throws ExceptionInterface
     */
    public function testListsTheTable(): void
    {
        self::assertContains($this->tableName, $this->client->listTables()->tableNames->toArray());
    }

    /**
     * @throws ExceptionInterface
     * @throws InvalidArgumentException
     */
    public function testPutsAndGetsAnItemWithEveryAttributeType(): void
    {
        $this->client->putItem(new PutItemRequest(
            item: new AttributeValueMap([
                'Artist' => AttributeValue::string('No One You Know'),
                'SongTitle' => AttributeValue::string('Call Me Today'),
                'Year' => AttributeValue::number(2015),
                'Genres' => AttributeValue::stringSet('Country', 'Pop'),
                'Ratings' => AttributeValue::numberSet(4, 5),
                'Awards' => AttributeValue::map(['Grammy' => AttributeValue::bool(false)]),
                'Tracks' => AttributeValue::list(AttributeValue::string('A'), AttributeValue::number(1)),
                'Cover' => AttributeValue::blob('aGVsbG8='),
                'Label' => AttributeValue::null(),
            ]),
            tableName: $this->tableName,
        ));

        $item = $this->client->getItem(new GetItemRequest(
            key: $this->key('No One You Know', 'Call Me Today'),
            tableName: $this->tableName,
            consistentRead: true,
        ))->item;

        self::assertInstanceOf(AttributeValueMap::class, $item);
        self::assertSame('2015', $item->get('Year')?->number);
        self::assertSame(['Country', 'Pop'], $this->sorted($item->get('Genres')?->stringSet?->toArray() ?? []));
        self::assertSame(['4', '5'], $this->sorted($item->get('Ratings')?->numberSet?->toArray() ?? []));
        self::assertFalse($item->get('Awards')?->map?->get('Grammy')?->bool);
        self::assertSame(
            ['A', null],
            array_map(
                static fn(AttributeValue $value): ?string => $value->string,
                $item->get('Tracks')?->list?->toArray() ?? [],
            ),
        );
        self::assertSame('aGVsbG8=', $item->get('Cover')?->blob);
        self::assertTrue($item->get('Label')?->null);
    }

    /**
     * @throws ExceptionInterface
     * @throws InvalidArgumentException
     */
    public function testReportsAMissingItemAsNull(): void
    {
        $response = $this->client->getItem(new GetItemRequest(
            key: $this->key('Nobody', 'Nothing'),
            tableName: $this->tableName,
        ));

        self::assertNull($response->item);
    }

    /**
     * @throws ExceptionInterface
     * @throws InvalidArgumentException
     * @throws JsonException
     */
    public function testReportsAFailedConditionAsABadResponse(): void
    {
        $this->putSong('No One You Know', 'Call Me Today');

        try {
            $this->client->putItem(new PutItemRequest(
                item: $this->key('No One You Know', 'Call Me Today'),
                tableName: $this->tableName,
                conditionExpression: 'attribute_not_exists(SongTitle)',
            ));
            self::fail('The conditional put must fail.');
        } catch (BadResponseException $exception) {
            $error = json_decode($exception->response->getBody()->__toString(), true, 512, JSON_THROW_ON_ERROR);

            self::assertSame(400, $exception->response->getStatusCode());
            self::assertIsArray($error);
            self::assertIsString($error['__type'] ?? null);
            self::assertStringEndsWith('#ConditionalCheckFailedException', $error['__type']);
        }
    }

    /**
     * @throws ExceptionInterface
     * @throws InvalidArgumentException
     */
    public function testPaginatesAQuery(): void
    {
        foreach (['Song A', 'Song B', 'Song C'] as $songTitle) {
            $this->putSong('No One You Know', $songTitle);
        }

        $this->putSong('Somebody Else', 'Song D');

        $songTitles = [];
        $pages = 0;
        $startKey = null;

        do {
            $page = $this->client->query(new QueryRequest(
                tableName: $this->tableName,
                exclusiveStartKey: $startKey,
                expressionAttributeValues: new AttributeValueMap([
                    ':artist' => AttributeValue::string('No One You Know'),
                ]),
                keyConditionExpression: 'Artist = :artist',
                limit: 2,
            ));

            foreach ($page->items as $item) {
                $songTitles[] = $item->get('SongTitle')?->string;
            }

            ++$pages;
            $startKey = $page->lastEvaluatedKey;
        } while ($startKey instanceof AttributeValueMap);

        self::assertSame(['Song A', 'Song B', 'Song C'], $songTitles);
        self::assertSame(2, $pages);
    }

    /**
     * @throws ExceptionInterface
     * @throws InvalidArgumentException
     */
    public function testWritesAndGetsABatch(): void
    {
        $this->client->batchWriteItem(new BatchWriteItemRequest(
            requestItems: new WriteRequestListMap([
                $this->tableName => new WriteRequestList([
                    WriteRequest::put($this->key('No One You Know', 'Song A')),
                    WriteRequest::put($this->key('No One You Know', 'Song B')),
                ]),
            ]),
        ));

        $response = $this->client->batchGetItem(new BatchGetItemRequest(
            requestItems: new KeysAndAttributesMap([
                $this->tableName => new KeysAndAttributes(new KeyList([
                    $this->key('No One You Know', 'Song A'),
                    $this->key('No One You Know', 'Song B'),
                ])),
            ]),
        ));

        $songTitles = array_map(
            static fn(AttributeValueMap $item): ?string => $item->get('SongTitle')?->string,
            $response->responses->get($this->tableName)?->toArray() ?? [],
        );

        self::assertSame(['Song A', 'Song B'], $this->sorted($songTitles));
        self::assertTrue($response->unprocessedKeys->isEmpty());
    }

    /**
     * @throws ExceptionInterface
     * @throws InvalidArgumentException
     */
    public function testRunsATransaction(): void
    {
        $this->putSong('No One You Know', 'Song A');

        $this->client->transactWriteItems(new TransactWriteItemsRequest(
            transactItems: new TransactWriteItemList([
                TransactWriteItem::put($this->key('No One You Know', 'Song B'), $this->tableName),
                TransactWriteItem::update(
                    key: $this->key('No One You Know', 'Song A'),
                    tableName: $this->tableName,
                    updateExpression: 'SET Plays = :plays',
                    expressionAttributeValues: new AttributeValueMap([':plays' => AttributeValue::number(1)]),
                ),
            ]),
        ));

        $response = $this->client->transactGetItems(new TransactGetItemsRequest(
            transactItems: new TransactGetItemList([
                TransactGetItem::get($this->key('No One You Know', 'Song A'), $this->tableName),
                TransactGetItem::get($this->key('No One You Know', 'Song B'), $this->tableName),
            ]),
        ));

        self::assertCount(2, $response->responses);
        self::assertSame('1', $response->responses->get(0)?->item?->get('Plays')?->number);
        self::assertSame('Song B', $response->responses->get(1)?->item?->get('SongTitle')?->string);
    }

    /**
     * @throws ExceptionInterface
     * @throws InvalidArgumentException
     */
    public function testRunsAPartiQlStatement(): void
    {
        $this->client->executeStatement(new ExecuteStatementRequest(
            statement: sprintf('INSERT INTO "%s" VALUE {\'Artist\': ?, \'SongTitle\': ?}', $this->tableName),
            parameters: new AttributeValueList([
                AttributeValue::string('No One You Know'),
                AttributeValue::string('Song A'),
            ]),
        ));

        $response = $this->client->executeStatement(new ExecuteStatementRequest(
            statement: sprintf('SELECT SongTitle FROM "%s" WHERE Artist = ?', $this->tableName),
            parameters: new AttributeValueList([AttributeValue::string('No One You Know')]),
        ));

        self::assertCount(1, $response->items);
        self::assertSame('Song A', $response->items->get(0)?->get('SongTitle')?->string);
    }

    /**
     * @throws ExceptionInterface
     * @throws InvalidArgumentException
     */
    private function putSong(string $artist, string $songTitle): void
    {
        $this->client->putItem(new PutItemRequest(
            item: $this->key($artist, $songTitle),
            tableName: $this->tableName,
        ));
    }

    /**
     * @throws InvalidArgumentException
     */
    private function key(string $artist, string $songTitle): AttributeValueMap
    {
        return new AttributeValueMap([
            'Artist' => AttributeValue::string($artist),
            'SongTitle' => AttributeValue::string($songTitle),
        ]);
    }

    /**
     * @template T of null|string
     * @param array<T> $values
     * @return list<T>
     */
    private function sorted(array $values): array
    {
        sort($values);

        return $values;
    }
}
