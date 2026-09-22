<?php

declare(strict_types=1);

namespace Imper86\DynamoDBClientTests\Serializer;

use InvalidArgumentException;
use Imper86\DynamoDBClient\Message\CreateTableRequest;
use Imper86\DynamoDBClient\Message\GetItemRequest;
use Imper86\DynamoDBClient\Message\QueryResponse;
use Imper86\DynamoDBClient\Model\AttributeValue;
use Imper86\DynamoDBClient\Model\AttributeValueMap;
use Imper86\DynamoDBClient\Model\ReturnConsumedCapacity;
use Imper86\DynamoDBClient\Model\SSESpecification;
use Imper86\DynamoDBClient\Serializer\SerializerFactory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Serializer\Exception\ExceptionInterface;
use Symfony\Component\Serializer\Exception\NotNormalizableValueException;

/**
 * @internal
 */
#[CoversClass(SerializerFactory::class)]
final class SerializerFactoryTest extends TestCase
{
    /**
     * @throws ExceptionInterface
     * @throws InvalidArgumentException
     */
    public function testSerializesGetItemRequest(): void
    {
        $request = new GetItemRequest(
            key: new AttributeValueMap([
                'ForumName' => new AttributeValue(string: 'Amazon DynamoDB'),
                'Subject' => new AttributeValue(string: 'How do I update multiple items?'),
            ]),
            tableName: 'Thread',
            consistentRead: true,
            projectionExpression: 'LastPostDateTime, Message, Tags',
            returnConsumedCapacity: ReturnConsumedCapacity::TOTAL,
        );

        $json = SerializerFactory::create()->serialize($request, 'json');

        self::assertJsonStringEqualsJsonFile(__DIR__ . '/../fixtures/get-item-request.json', $json);
    }

    /**
     * @throws ExceptionInterface
     * @throws InvalidArgumentException
     */
    public function testSerializesAnObjectWithEveryMemberNullAsAnEmptyJsonObject(): void
    {
        $json = SerializerFactory::create()->serialize(new SSESpecification(), 'json');

        self::assertSame('{}', $json);
    }

    /**
     * @throws ExceptionInterface
     * @throws InvalidArgumentException
     */
    public function testSerializesANestedObjectWithEveryMemberNullAsAnEmptyJsonObject(): void
    {
        $request = new CreateTableRequest(tableName: 'Music', sseSpecification: new SSESpecification());

        $json = SerializerFactory::create()->serialize($request, 'json');

        self::assertSame('{"TableName":"Music","SSESpecification":{}}', $json);
    }

    /**
     * Symfony's PropertyNormalizer would cast each of these scalars to an array and build the object from
     * its defaults, answering a malformed body with an empty value instead of an error.
     *
     * @return iterable<string, array{string}>
     */
    public static function provideRejectsAScalarWhereTheResponseHoldsAnObjectCases(): iterable
    {
        yield 'a collection' => ['{"Items":"x"}'];

        yield 'an item of a collection' => ['{"Items":["x"]}'];

        yield 'a map' => ['{"LastEvaluatedKey":1}'];

        yield 'a model' => ['{"ConsumedCapacity":"x"}'];

        yield 'the whole response' => ['"x"'];
    }

    /**
     * @throws ExceptionInterface
     */
    #[DataProvider('provideRejectsAScalarWhereTheResponseHoldsAnObjectCases')]
    public function testRejectsAScalarWhereTheResponseHoldsAnObject(string $json): void
    {
        $this->expectException(NotNormalizableValueException::class);

        SerializerFactory::create()->deserialize($json, QueryResponse::class, 'json');
    }

    /**
     * @throws ExceptionInterface
     */
    public function testReadsAMemberSentAsNullAsIfItWereAbsent(): void
    {
        $response = SerializerFactory::create()->deserialize(
            '{"Items":null,"LastEvaluatedKey":null,"ConsumedCapacity":null}',
            QueryResponse::class,
            'json',
        );

        self::assertInstanceOf(QueryResponse::class, $response);
        self::assertTrue($response->items->isEmpty());
        self::assertNull($response->lastEvaluatedKey);
        self::assertNull($response->consumedCapacity);
    }
}
