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
use Imper86\DynamoDBClient\Message\DeleteItemRequest;
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
 * The messages exchanged here are the "Delete an Item" example of the DeleteItem reference.
 *
 * @see https://docs.aws.amazon.com/amazondynamodb/latest/APIReference/API_DeleteItem.html
 * @internal
 */
#[CoversClass(DynamoDBClient::class)]
final class DeleteItemTest extends TestCase
{
    private const REQUEST_FIXTURE = __DIR__ . '/fixtures/delete-item-request.json';

    private const RESPONSE_FIXTURE = __DIR__ . '/fixtures/delete-item-response.json';

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

        $this->createClient($httpClient)->deleteItem($this->documentedRequest());

        $sent = $httpClient->getLastRequest();

        self::assertInstanceOf(Request::class, $sent);
        self::assertSame('DynamoDB_20120810.DeleteItem', $sent->getHeaderLine('X-Amz-Target'));
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

        $this->createClient($httpClient)->deleteItem(new DeleteItemRequest(
            key: new AttributeValueMap(['ForumName' => AttributeValue::string('Amazon DynamoDB')]),
            tableName: 'Thread',
            conditionalOperator: ConditionalOperator::OR,
            conditionExpression: '#R = :zero',
            expected: new ExpectedAttributeValueMap([
                'Replies' => ExpectedAttributeValue::comparison(ComparisonOperator::NULL),
                'Views' => ExpectedAttributeValue::value(AttributeValue::number(0)),
                'Tags' => ExpectedAttributeValue::notExists(),
                'Rank' => ExpectedAttributeValue::comparison(
                    ComparisonOperator::BETWEEN,
                    AttributeValue::number(1),
                    AttributeValue::number(5),
                ),
            ]),
            expressionAttributeNames: new NonEmptyStringMap(['#R' => 'Replies']),
            expressionAttributeValues: new AttributeValueMap([':zero' => AttributeValue::number(0)]),
            returnConsumedCapacity: ReturnConsumedCapacity::TOTAL,
            returnItemCollectionMetrics: ReturnItemCollectionMetrics::SIZE,
            returnValues: ReturnValue::NONE,
            returnValuesOnConditionCheckFailure: ReturnValuesOnConditionCheckFailure::ALL_OLD,
        ));

        $sent = $httpClient->getLastRequest();

        self::assertInstanceOf(Request::class, $sent);
        self::assertJsonStringEqualsJsonString(
            '{"Key":{"ForumName":{"S":"Amazon DynamoDB"}},"TableName":"Thread","ConditionalOperator":"OR",'
            . '"ConditionExpression":"#R = :zero","Expected":{'
            . '"Replies":{"ComparisonOperator":"NULL"},'
            . '"Views":{"Value":{"N":"0"}},'
            . '"Tags":{"Exists":false},'
            . '"Rank":{"AttributeValueList":[{"N":"1"},{"N":"5"}],"ComparisonOperator":"BETWEEN"}},'
            . '"ExpressionAttributeNames":{"#R":"Replies"},"ExpressionAttributeValues":{":zero":{"N":"0"}},'
            . '"ReturnConsumedCapacity":"TOTAL","ReturnItemCollectionMetrics":"SIZE","ReturnValues":"NONE",'
            . '"ReturnValuesOnConditionCheckFailure":"ALL_OLD"}',
            $sent->getBody()->__toString(),
        );
    }

    /**
     * @throws ExceptionInterface
     * @throws InvalidArgumentException
     * @throws MissingCredentialsException
     * @throws NotFoundException
     */
    public function testReturnsTheItemAsItWasBeforeTheDelete(): void
    {
        $httpClient = new MockClient();
        $httpClient->addResponse($this->documentedResponse());

        $response = $this->createClient($httpClient)->deleteItem($this->documentedRequest());

        $attributes = $response->attributes;

        self::assertInstanceOf(AttributeValueMap::class, $attributes);
        self::assertSame(
            ['LastPostedBy', 'ForumName', 'LastPostDateTime', 'Tags', 'Subject', 'Message'],
            $attributes->keys(),
        );
        self::assertSame('fred@example.com', $attributes->get('LastPostedBy')?->string);

        $tags = $attributes->get('Tags')?->stringSet;

        self::assertInstanceOf(StringSet::class, $tags);
        self::assertSame(['Update', 'Multiple Items', 'HelpMe'], $tags->toArray());
        self::assertNull($response->itemCollectionMetrics);
        self::assertNull($response->consumedCapacity);
    }

    /**
     * Without `ReturnValues` set to `ALL_OLD`, a successful delete answers with an empty object.
     *
     * @throws ExceptionInterface
     * @throws InvalidArgumentException
     * @throws MissingCredentialsException
     * @throws NotFoundException
     */
    public function testLeavesTheAttributesNullWhenTheServiceOmitsThem(): void
    {
        $httpClient = new MockClient();
        $httpClient->addResponse(new Response(body: '{}'));

        $response = $this->createClient($httpClient)->deleteItem($this->documentedRequest());

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
    public function testReturnsTheItemCollectionMetrics(): void
    {
        $httpClient = new MockClient();
        $httpClient->addResponse(new Response(body: '{"ItemCollectionMetrics":{'
            . '"ItemCollectionKey":{"ForumName":{"S":"Amazon DynamoDB"}},'
            . '"SizeEstimateRangeGB":[0.5,1]}}'));

        $response = $this->createClient($httpClient)->deleteItem($this->documentedRequest());

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

        $response = $this->createClient($httpClient)->deleteItem($this->documentedRequest());

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

        $this->createClient($httpClient)->deleteItem($this->documentedRequest());
    }

    /**
     * @throws InvalidArgumentException
     */
    public function testRejectsATableNameLongerThanTheServiceAllows(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new DeleteItemRequest(key: $this->documentedKey(), tableName: str_repeat('a', 1025));
    }

    /**
     * @throws InvalidArgumentException
     */
    public function testRejectsAReturnValueDeleteItemDoesNotRecognise(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new DeleteItemRequest(key: $this->documentedKey(), tableName: 'Thread', returnValues: ReturnValue::ALL_NEW);
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
    private function documentedKey(): AttributeValueMap
    {
        return new AttributeValueMap([
            'ForumName' => AttributeValue::string('Amazon DynamoDB'),
            'Subject' => AttributeValue::string('How do I update multiple items?'),
        ]);
    }

    /**
     * @throws InvalidArgumentException
     */
    private function documentedRequest(): DeleteItemRequest
    {
        return new DeleteItemRequest(
            key: $this->documentedKey(),
            tableName: 'Thread',
            conditionExpression: 'attribute_not_exists(Replies)',
            returnValues: ReturnValue::ALL_OLD,
        );
    }

    private function documentedResponse(): Response
    {
        $body = file_get_contents(self::RESPONSE_FIXTURE);

        self::assertIsString($body);

        return new Response(body: $body);
    }
}
