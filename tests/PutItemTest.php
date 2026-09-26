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
use Imper86\DynamoDBClient\Message\PutItemRequest;
use Imper86\DynamoDBClient\Model\AttributeValue;
use Imper86\DynamoDBClient\Model\AttributeValueMap;
use Imper86\DynamoDBClient\Model\ComparisonOperator;
use Imper86\DynamoDBClient\Model\ConditionalOperator;
use Imper86\DynamoDBClient\Model\ConsumedCapacity;
use Imper86\DynamoDBClient\Model\Credentials;
use Imper86\DynamoDBClient\Model\ExpectedAttributeValue;
use Imper86\DynamoDBClient\Model\ExpectedAttributeValueMap;
use Imper86\DynamoDBClient\Model\ItemCollectionMetrics;
use Imper86\DynamoDBClient\Model\ReturnConsumedCapacity;
use Imper86\DynamoDBClient\Model\ReturnItemCollectionMetrics;
use Imper86\DynamoDBClient\Model\ReturnValue;
use Imper86\DynamoDBClient\Model\ReturnValuesOnConditionCheckFailure;
use Imper86\DynamoDBClient\ValueObject\DoubleList;
use Imper86\DynamoDBClient\ValueObject\NonEmptyStringMap;
use Imper86\DynamoDBClient\ValueObject\StringSet;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

use function file_get_contents;
use function str_repeat;

/**
 * The messages exchanged here are the "Put an Item" example of the PutItem reference. Its response is an empty
 * object, so the response elements are covered by inline bodies.
 *
 * @see https://docs.aws.amazon.com/amazondynamodb/latest/APIReference/API_PutItem.html
 * @internal
 */
#[CoversClass(DynamoDBClient::class)]
final class PutItemTest extends TestCase
{
    private const REQUEST_FIXTURE = __DIR__ . '/fixtures/put-item-request.json';

    private const RESPONSE_FIXTURE = __DIR__ . '/fixtures/put-item-response.json';

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

        $this->createClient($httpClient)->putItem($this->documentedRequest());

        $sent = $httpClient->getLastRequest();

        self::assertInstanceOf(Request::class, $sent);
        self::assertSame('DynamoDB_20120810.PutItem', $sent->getHeaderLine('X-Amz-Target'));
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

        $this->createClient($httpClient)->putItem(new PutItemRequest(
            item: new AttributeValueMap(['ForumName' => AttributeValue::string('Amazon DynamoDB')]),
            tableName: 'Thread',
            conditionalOperator: ConditionalOperator::AND,
            conditionExpression: 'attribute_not_exists(#F)',
            expected: new ExpectedAttributeValueMap([
                'Replies' => ExpectedAttributeValue::comparison(ComparisonOperator::NULL),
                'Views' => ExpectedAttributeValue::value(AttributeValue::number(0)),
            ]),
            expressionAttributeNames: new NonEmptyStringMap(['#F' => 'ForumName']),
            expressionAttributeValues: new AttributeValueMap([':zero' => AttributeValue::number(0)]),
            returnConsumedCapacity: ReturnConsumedCapacity::TOTAL,
            returnItemCollectionMetrics: ReturnItemCollectionMetrics::SIZE,
            returnValues: ReturnValue::ALL_OLD,
            returnValuesOnConditionCheckFailure: ReturnValuesOnConditionCheckFailure::ALL_OLD,
        ));

        $sent = $httpClient->getLastRequest();

        self::assertInstanceOf(Request::class, $sent);
        self::assertJsonStringEqualsJsonString(
            '{"Item":{"ForumName":{"S":"Amazon DynamoDB"}},"TableName":"Thread","ConditionalOperator":"AND",'
            . '"ConditionExpression":"attribute_not_exists(#F)","Expected":{'
            . '"Replies":{"ComparisonOperator":"NULL"},'
            . '"Views":{"Value":{"N":"0"}}},'
            . '"ExpressionAttributeNames":{"#F":"ForumName"},"ExpressionAttributeValues":{":zero":{"N":"0"}},'
            . '"ReturnConsumedCapacity":"TOTAL","ReturnItemCollectionMetrics":"SIZE","ReturnValues":"ALL_OLD",'
            . '"ReturnValuesOnConditionCheckFailure":"ALL_OLD"}',
            $sent->getBody()->__toString(),
        );
    }

    /**
     * A put that did not ask for `ALL_OLD`, as in the documented example, answers with an empty object.
     *
     * @throws ExceptionInterface
     * @throws InvalidArgumentException
     * @throws MissingCredentialsException
     * @throws NotFoundException
     */
    public function testLeavesEveryElementNullWhenTheServiceOmitsThem(): void
    {
        $httpClient = new MockClient();
        $httpClient->addResponse($this->documentedResponse());

        $response = $this->createClient($httpClient)->putItem($this->documentedRequest());

        self::assertNull($response->attributes);
        self::assertNull($response->itemCollectionMetrics);
        self::assertNull($response->consumedCapacity);
    }

    /**
     * @throws ExceptionInterface
     * @throws InvalidArgumentException
     * @throws MissingCredentialsException
     * @throws NotFoundException
     */
    public function testReturnsTheItemThePutReplaced(): void
    {
        $httpClient = new MockClient();
        $httpClient->addResponse(new Response(body: '{"Attributes":{'
            . '"ForumName":{"S":"Amazon DynamoDB"},'
            . '"Subject":{"S":"How do I update multiple items?"},'
            . '"Tags":{"SS":["Update","Multiple Items"]}}}'));

        $response = $this->createClient($httpClient)->putItem($this->documentedRequest());

        $attributes = $response->attributes;

        self::assertInstanceOf(AttributeValueMap::class, $attributes);
        self::assertSame(['ForumName', 'Subject', 'Tags'], $attributes->keys());
        self::assertSame('Amazon DynamoDB', $attributes->get('ForumName')?->string);

        $tags = $attributes->get('Tags')?->stringSet;

        self::assertInstanceOf(StringSet::class, $tags);
        self::assertSame(['Update', 'Multiple Items'], $tags->toArray());
    }

    /**
     * @throws ExceptionInterface
     * @throws InvalidArgumentException
     * @throws MissingCredentialsException
     * @throws NotFoundException
     */
    public function testReturnsTheItemCollectionMetrics(): void
    {
        $httpClient = new MockClient();
        $httpClient->addResponse(new Response(body: '{"ItemCollectionMetrics":{'
            . '"ItemCollectionKey":{"ForumName":{"S":"Amazon DynamoDB"}},'
            . '"SizeEstimateRangeGB":[0.5,1]}}'));

        $response = $this->createClient($httpClient)->putItem($this->documentedRequest());

        $metrics = $response->itemCollectionMetrics;

        self::assertInstanceOf(ItemCollectionMetrics::class, $metrics);
        self::assertSame('Amazon DynamoDB', $metrics->itemCollectionKey?->get('ForumName')?->string);

        $range = $metrics->sizeEstimateRangeGB;

        self::assertInstanceOf(DoubleList::class, $range);
        self::assertSame([0.5, 1], $range->toArray());
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
        $httpClient->addResponse(new Response(body: '{"ConsumedCapacity":{"CapacityUnits":1,"TableName":"Thread"}}'));

        $response = $this->createClient($httpClient)->putItem($this->documentedRequest());

        $consumedCapacity = $response->consumedCapacity;

        self::assertInstanceOf(ConsumedCapacity::class, $consumedCapacity);
        self::assertSame(1.0, $consumedCapacity->capacityUnits);
        self::assertSame('Thread', $consumedCapacity->tableName);
    }

    /**
     * Attributes that are a JSON array cannot become an {@see AttributeValueMap}.
     *
     * @throws ExceptionInterface
     * @throws InvalidArgumentException
     * @throws MissingCredentialsException
     * @throws NotFoundException
     */
    public function testWrapsAResponseItCannotDeserialize(): void
    {
        $httpClient = new MockClient();
        $httpClient->addResponse(new Response(body: '{"Attributes":[{"S":"Amazon DynamoDB"}]}'));

        $this->expectException(ResponseDeserializationException::class);

        $this->createClient($httpClient)->putItem($this->documentedRequest());
    }

    /**
     * @throws InvalidArgumentException
     */
    public function testRejectsATableNameLongerThanTheServiceAllows(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new PutItemRequest(item: $this->documentedItem(), tableName: str_repeat('a', 1025));
    }

    /**
     * @throws InvalidArgumentException
     */
    public function testRejectsAReturnValuePutItemDoesNotRecognise(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new PutItemRequest(item: $this->documentedItem(), tableName: 'Thread', returnValues: ReturnValue::ALL_NEW);
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
    private function documentedItem(): AttributeValueMap
    {
        return new AttributeValueMap([
            'LastPostDateTime' => AttributeValue::string('201303190422'),
            'Tags' => AttributeValue::stringSet('Update', 'Multiple Items', 'HelpMe'),
            'ForumName' => AttributeValue::string('Amazon DynamoDB'),
            'Message' => AttributeValue::string(
                "I want to update multiple items in a single call. What's the best way to do that?",
            ),
            'Subject' => AttributeValue::string('How do I update multiple items?'),
            'LastPostedBy' => AttributeValue::string('fred@example.com'),
        ]);
    }

    /**
     * @throws InvalidArgumentException
     */
    private function documentedRequest(): PutItemRequest
    {
        return new PutItemRequest(
            item: $this->documentedItem(),
            tableName: 'Thread',
            conditionExpression: 'ForumName <> :f and Subject <> :s',
            expressionAttributeValues: new AttributeValueMap([
                ':f' => AttributeValue::string('Amazon DynamoDB'),
                ':s' => AttributeValue::string('How do I update multiple items?'),
            ]),
        );
    }

    private function documentedResponse(): Response
    {
        $body = file_get_contents(self::RESPONSE_FIXTURE);

        self::assertIsString($body);

        return new Response(body: $body);
    }
}
