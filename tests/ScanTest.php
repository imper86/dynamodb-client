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
use Imper86\DynamoDBClient\Message\ScanRequest;
use Imper86\DynamoDBClient\Model\AttributeValue;
use Imper86\DynamoDBClient\Model\AttributeValueMap;
use Imper86\DynamoDBClient\Model\ComparisonOperator;
use Imper86\DynamoDBClient\Model\Condition;
use Imper86\DynamoDBClient\Model\ConditionalOperator;
use Imper86\DynamoDBClient\Model\ConditionMap;
use Imper86\DynamoDBClient\Model\ConsumedCapacity;
use Imper86\DynamoDBClient\Model\Credentials;
use Imper86\DynamoDBClient\Model\ItemList;
use Imper86\DynamoDBClient\Model\ReturnConsumedCapacity;
use Imper86\DynamoDBClient\Model\Select;
use Imper86\DynamoDBClient\ValueObject\NonEmptyStringList;
use Imper86\DynamoDBClient\ValueObject\NonEmptyStringMap;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

use function file_get_contents;
use function str_repeat;

/**
 * The messages exchanged here are the "Use a Filter Expression" example of the Scan reference.
 *
 * @see https://docs.aws.amazon.com/amazondynamodb/latest/APIReference/API_Scan.html
 * @internal
 */
#[CoversClass(DynamoDBClient::class)]
final class ScanTest extends TestCase
{
    private const string REQUEST_FIXTURE = __DIR__ . '/fixtures/scan-request.json';

    private const string RESPONSE_FIXTURE = __DIR__ . '/fixtures/scan-response.json';

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

        $this->createClient($httpClient)->scan($this->documentedRequest());

        $sent = $httpClient->getLastRequest();

        self::assertInstanceOf(Request::class, $sent);
        self::assertSame('DynamoDB_20120810.Scan', $sent->getHeaderLine('X-Amz-Target'));
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

        $this->createClient($httpClient)->scan(new ScanRequest(
            tableName: 'Reply',
            attributesToGet: new NonEmptyStringList(['Id', 'PostedBy']),
            conditionalOperator: ConditionalOperator::OR,
            consistentRead: false,
            exclusiveStartKey: new AttributeValueMap([
                'Id' => AttributeValue::string('Amazon DynamoDB#How do I update multiple items?'),
                'ReplyDateTime' => AttributeValue::string('20130320115336'),
            ]),
            expressionAttributeNames: new NonEmptyStringMap(['#P' => 'PostedBy']),
            expressionAttributeValues: new AttributeValueMap([':val' => AttributeValue::string('joe@example.com')]),
            filterExpression: '#P = :val',
            indexName: 'PostedBy-Index',
            limit: 10,
            projectionExpression: 'Id, PostedBy',
            returnConsumedCapacity: ReturnConsumedCapacity::INDEXES,
            scanFilter: new ConditionMap([
                'PostedBy' => Condition::comparison(ComparisonOperator::EQ, AttributeValue::string('joe@example.com')),
                'Message' => Condition::comparison(ComparisonOperator::NOT_NULL),
            ]),
            segment: 1,
            select: Select::SPECIFIC_ATTRIBUTES,
            totalSegments: 4,
        ));

        $sent = $httpClient->getLastRequest();

        self::assertInstanceOf(Request::class, $sent);
        self::assertJsonStringEqualsJsonString(
            '{"TableName":"Reply","AttributesToGet":["Id","PostedBy"],"ConditionalOperator":"OR",'
            . '"ConsistentRead":false,"ExclusiveStartKey":{'
            . '"Id":{"S":"Amazon DynamoDB#How do I update multiple items?"},"ReplyDateTime":{"S":"20130320115336"}},'
            . '"ExpressionAttributeNames":{"#P":"PostedBy"},"ExpressionAttributeValues":{":val":{"S":"joe@example.com"}},'
            . '"FilterExpression":"#P = :val","IndexName":"PostedBy-Index","Limit":10,'
            . '"ProjectionExpression":"Id, PostedBy","ReturnConsumedCapacity":"INDEXES",'
            . '"ScanFilter":{"PostedBy":{"ComparisonOperator":"EQ","AttributeValueList":[{"S":"joe@example.com"}]},'
            . '"Message":{"ComparisonOperator":"NOT_NULL"}},'
            . '"Segment":1,"Select":"SPECIFIC_ATTRIBUTES","TotalSegments":4}',
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

        $response = $this->createClient($httpClient)->scan($this->documentedRequest());

        self::assertCount(2, $response->items);

        $first = $response->items->get(0);

        self::assertInstanceOf(AttributeValueMap::class, $first);
        self::assertSame(['PostedBy', 'ReplyDateTime', 'Id', 'Message'], $first->keys());
        self::assertSame('20130320115336', $first->get('ReplyDateTime')?->string);
        self::assertSame('20130320115347', $response->items->get(1)?->get('ReplyDateTime')?->string);
        self::assertSame(2, $response->count);
        self::assertSame(4, $response->scannedCount);
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

        $response = $this->createClient($httpClient)->scan($this->documentedRequest());

        $consumedCapacity = $response->consumedCapacity;

        self::assertInstanceOf(ConsumedCapacity::class, $consumedCapacity);
        self::assertSame(0.5, $consumedCapacity->capacityUnits);
        self::assertSame('Reply', $consumedCapacity->tableName);
    }

    /**
     * @throws ExceptionInterface
     * @throws InvalidArgumentException
     * @throws MissingCredentialsException
     * @throws NotFoundException
     */
    public function testDefaultsToNoItemsWhenTheServiceOmitsThem(): void
    {
        $httpClient = new MockClient();
        $httpClient->addResponse(new Response(body: '{"Count":4,"ScannedCount":4}'));

        $response = $this->createClient($httpClient)->scan(new ScanRequest(tableName: 'Reply', select: Select::COUNT));

        self::assertTrue($response->items->isEmpty());
        self::assertSame(4, $response->count);
        self::assertSame(4, $response->scannedCount);
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

        $response = $this->createClient($httpClient)->scan($this->documentedRequest());

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
        $httpClient->addResponse(new Response(body: '{"Count":1,"ScannedCount":2,"LastEvaluatedKey":{'
            . '"Id":{"S":"Amazon DynamoDB#How do I update multiple items?"},"ReplyDateTime":{"S":"20130320115342"}}}'));

        $response = $this->createClient($httpClient)->scan($this->documentedRequest());

        $lastEvaluatedKey = $response->lastEvaluatedKey;

        self::assertInstanceOf(AttributeValueMap::class, $lastEvaluatedKey);
        self::assertSame(['Id', 'ReplyDateTime'], $lastEvaluatedKey->keys());
        self::assertSame('20130320115342', $lastEvaluatedKey->get('ReplyDateTime')?->string);
        self::assertSame(2, $response->scannedCount);
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
        $httpClient->addResponse(new Response(body: '{"Items":{"Id":{"S":"Amazon DynamoDB#How do I update multiple items?"}}}'));

        $this->expectException(ResponseDeserializationException::class);

        $this->createClient($httpClient)->scan($this->documentedRequest());
    }

    /**
     * @throws InvalidArgumentException
     */
    public function testRejectsATableNameLongerThanTheServiceAllows(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new ScanRequest(tableName: str_repeat('a', 1025));
    }

    /**
     * @throws InvalidArgumentException
     */
    public function testRejectsAnEmptyAttributesToGet(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new ScanRequest(tableName: 'Reply', attributesToGet: new NonEmptyStringList([]));
    }

    /**
     * @throws InvalidArgumentException
     */
    public function testRejectsAnIndexNameShorterThanThreeCharacters(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new ScanRequest(tableName: 'Reply', indexName: 'Po');
    }

    /**
     * @throws InvalidArgumentException
     */
    public function testRejectsAnIndexNameLongerThanTwoHundredAndFiftyFiveCharacters(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new ScanRequest(tableName: 'Reply', indexName: str_repeat('a', 256));
    }

    /**
     * @throws InvalidArgumentException
     */
    public function testRejectsAnIndexNameWithACharacterThePatternDoesNotAllow(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new ScanRequest(tableName: 'Reply', indexName: 'PostedBy Index');
    }

    /**
     * @throws InvalidArgumentException
     */
    public function testRejectsASegmentWithoutTotalSegments(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new ScanRequest(tableName: 'Reply', segment: 0);
    }

    /**
     * @throws InvalidArgumentException
     */
    public function testRejectsTotalSegmentsWithoutASegment(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new ScanRequest(tableName: 'Reply', totalSegments: 4);
    }

    /**
     * Segments count from zero, so the last of four is 3.
     *
     * @throws InvalidArgumentException
     */
    public function testRejectsASegmentThatIsNotBelowTotalSegments(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new ScanRequest(tableName: 'Reply', segment: 4, totalSegments: 4);
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
    private function documentedRequest(): ScanRequest
    {
        return new ScanRequest(
            tableName: 'Reply',
            expressionAttributeValues: new AttributeValueMap([':val' => AttributeValue::string('joe@example.com')]),
            filterExpression: 'PostedBy = :val',
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
