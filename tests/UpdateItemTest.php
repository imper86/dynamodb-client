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
use Imper86\DynamoDBClient\Message\UpdateItemRequest;
use Imper86\DynamoDBClient\Model\AttributeValue;
use Imper86\DynamoDBClient\Model\AttributeValueMap;
use Imper86\DynamoDBClient\Model\AttributeValueUpdate;
use Imper86\DynamoDBClient\Model\AttributeValueUpdateMap;
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
 * The messages exchanged here are the "Conditional Update" example of the UpdateItem reference.
 *
 * @see https://docs.aws.amazon.com/amazondynamodb/latest/APIReference/API_UpdateItem.html
 * @internal
 */
#[CoversClass(DynamoDBClient::class)]
final class UpdateItemTest extends TestCase
{
    private const REQUEST_FIXTURE = __DIR__ . '/fixtures/update-item-request.json';

    private const RESPONSE_FIXTURE = __DIR__ . '/fixtures/update-item-response.json';

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

        $this->createClient($httpClient)->updateItem($this->documentedRequest());

        $sent = $httpClient->getLastRequest();

        self::assertInstanceOf(Request::class, $sent);
        self::assertSame('DynamoDB_20120810.UpdateItem', $sent->getHeaderLine('X-Amz-Target'));
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

        $this->createClient($httpClient)->updateItem(new UpdateItemRequest(
            key: new AttributeValueMap(['ForumName' => AttributeValue::string('Amazon DynamoDB')]),
            tableName: 'Thread',
            attributeUpdates: new AttributeValueUpdateMap([
                'LastPostedBy' => AttributeValueUpdate::put(AttributeValue::string('alice@example.com')),
                'Replies' => AttributeValueUpdate::add(AttributeValue::number(1)),
                'Tags' => AttributeValueUpdate::delete(AttributeValue::stringSet('HelpMe')),
                'Draft' => AttributeValueUpdate::delete(),
            ]),
            conditionalOperator: ConditionalOperator::AND,
            conditionExpression: '#R > :zero',
            expected: new ExpectedAttributeValueMap([
                'LastPostedBy' => ExpectedAttributeValue::value(AttributeValue::string('fred@example.com')),
            ]),
            expressionAttributeNames: new NonEmptyStringMap(['#R' => 'Replies']),
            expressionAttributeValues: new AttributeValueMap([':zero' => AttributeValue::number(0)]),
            returnConsumedCapacity: ReturnConsumedCapacity::TOTAL,
            returnItemCollectionMetrics: ReturnItemCollectionMetrics::SIZE,
            returnValues: ReturnValue::UPDATED_OLD,
            returnValuesOnConditionCheckFailure: ReturnValuesOnConditionCheckFailure::ALL_OLD,
            updateExpression: 'SET #R = #R + :zero',
        ));

        $sent = $httpClient->getLastRequest();

        self::assertInstanceOf(Request::class, $sent);
        self::assertJsonStringEqualsJsonString(
            '{"Key":{"ForumName":{"S":"Amazon DynamoDB"}},"TableName":"Thread","AttributeUpdates":{'
            . '"LastPostedBy":{"Action":"PUT","Value":{"S":"alice@example.com"}},'
            . '"Replies":{"Action":"ADD","Value":{"N":"1"}},'
            . '"Tags":{"Action":"DELETE","Value":{"SS":["HelpMe"]}},'
            . '"Draft":{"Action":"DELETE"}},'
            . '"ConditionalOperator":"AND","ConditionExpression":"#R > :zero",'
            . '"Expected":{"LastPostedBy":{"Value":{"S":"fred@example.com"}}},'
            . '"ExpressionAttributeNames":{"#R":"Replies"},"ExpressionAttributeValues":{":zero":{"N":"0"}},'
            . '"ReturnConsumedCapacity":"TOTAL","ReturnItemCollectionMetrics":"SIZE","ReturnValues":"UPDATED_OLD",'
            . '"ReturnValuesOnConditionCheckFailure":"ALL_OLD","UpdateExpression":"SET #R = #R + :zero"}',
            $sent->getBody()->__toString(),
        );
    }

    /**
     * @throws ExceptionInterface
     * @throws InvalidArgumentException
     * @throws MissingCredentialsException
     * @throws NotFoundException
     */
    public function testReturnsTheItemAsItIsAfterTheUpdate(): void
    {
        $httpClient = new MockClient();
        $httpClient->addResponse($this->documentedResponse());

        $response = $this->createClient($httpClient)->updateItem($this->documentedRequest());

        $attributes = $response->attributes;

        self::assertInstanceOf(AttributeValueMap::class, $attributes);
        self::assertSame(
            ['LastPostedBy', 'ForumName', 'LastPostDateTime', 'Tags', 'Subject', 'Views', 'Message'],
            $attributes->keys(),
        );
        self::assertSame('alice@example.com', $attributes->get('LastPostedBy')?->string);
        self::assertSame('5', $attributes->get('Views')?->number);

        $tags = $attributes->get('Tags')?->stringSet;

        self::assertInstanceOf(StringSet::class, $tags);
        self::assertSame(['Update', 'Multiple Items', 'HelpMe'], $tags->toArray());
        self::assertNull($response->itemCollectionMetrics);
        self::assertNull($response->consumedCapacity);
    }

    /**
     * The "Atomic Counter" example: with `ReturnValues` set to `NONE`, the service answers with an empty object.
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

        $response = $this->createClient($httpClient)->updateItem(new UpdateItemRequest(
            key: new AttributeValueMap([
                'ForumName' => AttributeValue::string('Amazon DynamoDB'),
                'Subject' => AttributeValue::string('A question about updates'),
            ]),
            tableName: 'Thread',
            expressionAttributeValues: new AttributeValueMap([':num' => AttributeValue::number(1)]),
            returnValues: ReturnValue::NONE,
            updateExpression: 'set Replies = Replies + :num',
        ));

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

        $response = $this->createClient($httpClient)->updateItem($this->documentedRequest());

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

        $response = $this->createClient($httpClient)->updateItem($this->documentedRequest());

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

        $this->createClient($httpClient)->updateItem($this->documentedRequest());
    }

    /**
     * @throws InvalidArgumentException
     */
    public function testRejectsATableNameLongerThanTheServiceAllows(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new UpdateItemRequest(key: $this->documentedKey(), tableName: str_repeat('a', 1025));
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
            'Subject' => AttributeValue::string('Maximum number of items?'),
        ]);
    }

    /**
     * @throws InvalidArgumentException
     */
    private function documentedRequest(): UpdateItemRequest
    {
        return new UpdateItemRequest(
            key: $this->documentedKey(),
            tableName: 'Thread',
            conditionExpression: 'LastPostedBy = :val2',
            expressionAttributeValues: new AttributeValueMap([
                ':val1' => AttributeValue::string('alice@example.com'),
                ':val2' => AttributeValue::string('fred@example.com'),
            ]),
            returnValues: ReturnValue::ALL_NEW,
            updateExpression: 'set LastPostedBy = :val1',
        );
    }

    private function documentedResponse(): Response
    {
        $body = file_get_contents(self::RESPONSE_FIXTURE);

        self::assertIsString($body);

        return new Response(body: $body);
    }
}
