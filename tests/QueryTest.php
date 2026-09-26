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
use Imper86\DynamoDBClient\Message\QueryRequest;
use Imper86\DynamoDBClient\Model\AttributeValue;
use Imper86\DynamoDBClient\Model\AttributeValueMap;
use Imper86\DynamoDBClient\Model\ComparisonOperator;
use Imper86\DynamoDBClient\Model\Condition;
use Imper86\DynamoDBClient\Model\ConditionalOperator;
use Imper86\DynamoDBClient\Model\ConditionMap;
use Imper86\DynamoDBClient\Model\ConsumedCapacity;
use Imper86\DynamoDBClient\Model\Credentials;
use Imper86\DynamoDBClient\Model\ReturnConsumedCapacity;
use Imper86\DynamoDBClient\Model\Select;
use Imper86\DynamoDBClient\ValueObject\NonEmptyStringList;
use Imper86\DynamoDBClient\ValueObject\NonEmptyStringMap;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Imper86\DynamoDBClient\Model\ItemList;

use function file_get_contents;
use function str_repeat;

/**
 * The messages exchanged here are the "Retrieve a Range of Items" example of the Query reference; the
 * "Count Items" example supplies the response without items.
 *
 * @see https://docs.aws.amazon.com/amazondynamodb/latest/APIReference/API_Query.html
 * @internal
 */
#[CoversClass(DynamoDBClient::class)]
final class QueryTest extends TestCase
{
    private const REQUEST_FIXTURE = __DIR__ . '/fixtures/query-request.json';

    private const RESPONSE_FIXTURE = __DIR__ . '/fixtures/query-response.json';

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

        $this->createClient($httpClient)->query($this->documentedRequest());

        $sent = $httpClient->getLastRequest();

        self::assertInstanceOf(Request::class, $sent);
        self::assertSame('DynamoDB_20120810.Query', $sent->getHeaderLine('X-Amz-Target'));
        self::assertJsonStringEqualsJsonFile(self::REQUEST_FIXTURE, $sent->getBody()->__toString());
    }

    /**
     * @throws ExceptionInterface
     * @throws InvalidArgumentException
     * @throws MissingCredentialsException
     * @throws NotFoundException
     */
    public function testSendsEveryOptionalParameter(): void
    {
        $httpClient = new MockClient();
        $httpClient->addResponse(new Response(body: '{}'));

        $this->createClient($httpClient)->query(new QueryRequest(
            tableName: 'Reply',
            attributesToGet: new NonEmptyStringList(['Id', 'PostedBy']),
            conditionalOperator: ConditionalOperator::AND,
            consistentRead: false,
            exclusiveStartKey: new AttributeValueMap(['Id' => AttributeValue::string('Amazon DynamoDB#DynamoDB Thread 1')]),
            expressionAttributeNames: new NonEmptyStringMap(['#P' => 'PostedBy']),
            expressionAttributeValues: new AttributeValueMap([':p' => AttributeValue::string('User A')]),
            filterExpression: '#P = :p',
            indexName: 'PostedBy-Index',
            keyConditionExpression: 'Id = :v1',
            keyConditions: new ConditionMap([
                'Id' => Condition::comparison(
                    ComparisonOperator::EQ,
                    AttributeValue::string('Amazon DynamoDB#DynamoDB Thread 1'),
                ),
            ]),
            limit: 10,
            projectionExpression: 'Id, PostedBy',
            queryFilter: new ConditionMap(['Message' => Condition::comparison(ComparisonOperator::NOT_NULL)]),
            returnConsumedCapacity: ReturnConsumedCapacity::INDEXES,
            scanIndexForward: false,
            select: Select::SPECIFIC_ATTRIBUTES,
        ));

        $sent = $httpClient->getLastRequest();

        self::assertInstanceOf(Request::class, $sent);
        self::assertJsonStringEqualsJsonString(
            '{"TableName":"Reply","AttributesToGet":["Id","PostedBy"],"ConditionalOperator":"AND",'
            . '"ConsistentRead":false,"ExclusiveStartKey":{"Id":{"S":"Amazon DynamoDB#DynamoDB Thread 1"}},'
            . '"ExpressionAttributeNames":{"#P":"PostedBy"},"ExpressionAttributeValues":{":p":{"S":"User A"}},'
            . '"FilterExpression":"#P = :p","IndexName":"PostedBy-Index","KeyConditionExpression":"Id = :v1",'
            . '"KeyConditions":{"Id":{"ComparisonOperator":"EQ",'
            . '"AttributeValueList":[{"S":"Amazon DynamoDB#DynamoDB Thread 1"}]}},'
            . '"Limit":10,"ProjectionExpression":"Id, PostedBy",'
            . '"QueryFilter":{"Message":{"ComparisonOperator":"NOT_NULL"}},'
            . '"ReturnConsumedCapacity":"INDEXES","ScanIndexForward":false,"Select":"SPECIFIC_ATTRIBUTES"}',
            $sent->getBody()->__toString(),
        );
    }

    /**
     * @throws ExceptionInterface
     * @throws InvalidArgumentException
     * @throws MissingCredentialsException
     * @throws NotFoundException
     */
    public function testReturnsTheMatchingItems(): void
    {
        $httpClient = new MockClient();
        $httpClient->addResponse($this->documentedResponse());

        $response = $this->createClient($httpClient)->query($this->documentedRequest());

        self::assertCount(2, $response->items);

        $first = $response->items->get(0);

        self::assertInstanceOf(AttributeValueMap::class, $first);
        self::assertSame(['ReplyDateTime', 'PostedBy', 'Id'], $first->keys());
        self::assertSame('User A', $first->get('PostedBy')?->string);
        self::assertSame('User B', $response->items->get(1)?->get('PostedBy')?->string);
        self::assertSame(2, $response->count);
        self::assertSame(2, $response->scannedCount);
        self::assertNull($response->lastEvaluatedKey);
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

        $response = $this->createClient($httpClient)->query($this->documentedRequest());

        $consumedCapacity = $response->consumedCapacity;

        self::assertInstanceOf(ConsumedCapacity::class, $consumedCapacity);
        self::assertSame(1.0, $consumedCapacity->capacityUnits);
        self::assertSame('Reply', $consumedCapacity->tableName);
    }

    /**
     * The "Count Items" example answers with the counts alone.
     *
     * @throws ExceptionInterface
     * @throws InvalidArgumentException
     * @throws MissingCredentialsException
     * @throws NotFoundException
     */
    public function testDefaultsToNoItemsWhenTheServiceOmitsThem(): void
    {
        $httpClient = new MockClient();
        $httpClient->addResponse(new Response(body: '{"Count":2,"ScannedCount":2}'));

        $response = $this->createClient($httpClient)->query($this->documentedRequest());

        self::assertTrue($response->items->isEmpty());
        self::assertSame(2, $response->count);
        self::assertSame(2, $response->scannedCount);
        self::assertNull($response->lastEvaluatedKey);
        self::assertNull($response->consumedCapacity);
    }

    /**
     * @throws ExceptionInterface
     * @throws InvalidArgumentException
     * @throws MissingCredentialsException
     * @throws NotFoundException
     */
    public function testLeavesTheCountsNullWhenTheServiceOmitsThem(): void
    {
        $httpClient = new MockClient();
        $httpClient->addResponse(new Response(body: '{}'));

        $response = $this->createClient($httpClient)->query($this->documentedRequest());

        self::assertTrue($response->items->isEmpty());
        self::assertNull($response->count);
        self::assertNull($response->scannedCount);
    }

    /**
     * @throws ExceptionInterface
     * @throws InvalidArgumentException
     * @throws MissingCredentialsException
     * @throws NotFoundException
     */
    public function testReturnsTheKeyTheNextPageStartsAt(): void
    {
        $httpClient = new MockClient();
        $httpClient->addResponse(new Response(body: '{"Count":1,"ScannedCount":3,"LastEvaluatedKey":{'
            . '"Id":{"S":"Amazon DynamoDB#DynamoDB Thread 1"},"ReplyDateTime":{"S":"2015-02-25T20:27:36.165Z"}}}'));

        $response = $this->createClient($httpClient)->query($this->documentedRequest());

        $lastEvaluatedKey = $response->lastEvaluatedKey;

        self::assertInstanceOf(AttributeValueMap::class, $lastEvaluatedKey);
        self::assertSame(['Id', 'ReplyDateTime'], $lastEvaluatedKey->keys());
        self::assertSame('2015-02-25T20:27:36.165Z', $lastEvaluatedKey->get('ReplyDateTime')?->string);
        self::assertSame(3, $response->scannedCount);
    }

    /**
     * Items that are a JSON object cannot become an {@see ItemList}.
     *
     * @throws ExceptionInterface
     * @throws InvalidArgumentException
     * @throws MissingCredentialsException
     * @throws NotFoundException
     */
    public function testWrapsAResponseItCannotDeserialize(): void
    {
        $httpClient = new MockClient();
        $httpClient->addResponse(new Response(body: '{"Items":{"Id":{"S":"Amazon DynamoDB#DynamoDB Thread 1"}}}'));

        $this->expectException(ResponseDeserializationException::class);

        $this->createClient($httpClient)->query($this->documentedRequest());
    }

    /**
     * @throws InvalidArgumentException
     */
    public function testRejectsATableNameLongerThanTheServiceAllows(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new QueryRequest(tableName: str_repeat('a', 1025));
    }

    /**
     * @throws InvalidArgumentException
     */
    public function testRejectsAnEmptyAttributesToGet(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new QueryRequest(tableName: 'Reply', attributesToGet: new NonEmptyStringList([]));
    }

    /**
     * @throws InvalidArgumentException
     */
    public function testRejectsAnIndexNameShorterThanThreeCharacters(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new QueryRequest(tableName: 'Reply', indexName: 'Po');
    }

    /**
     * @throws InvalidArgumentException
     */
    public function testRejectsAnIndexNameLongerThanTwoHundredAndFiftyFiveCharacters(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new QueryRequest(tableName: 'Reply', indexName: str_repeat('a', 256));
    }

    /**
     * @throws InvalidArgumentException
     */
    public function testRejectsAnIndexNameWithACharacterThePatternDoesNotAllow(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new QueryRequest(tableName: 'Reply', indexName: 'PostedBy Index');
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
    private function documentedRequest(): QueryRequest
    {
        return new QueryRequest(
            tableName: 'Reply',
            consistentRead: true,
            expressionAttributeValues: new AttributeValueMap([
                ':v1' => AttributeValue::string('Amazon DynamoDB#DynamoDB Thread 1'),
                ':v2a' => AttributeValue::string('User A'),
                ':v2b' => AttributeValue::string('User C'),
            ]),
            indexName: 'PostedBy-Index',
            keyConditionExpression: 'Id = :v1 AND PostedBy BETWEEN :v2a AND :v2b',
            limit: 3,
            projectionExpression: 'Id, PostedBy, ReplyDateTime',
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
