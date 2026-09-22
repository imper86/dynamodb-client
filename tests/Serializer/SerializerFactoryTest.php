<?php

declare(strict_types=1);

namespace Imper86\DynamoDBClientTests\Serializer;

use InvalidArgumentException;
use Imper86\DynamoDBClient\Message\CreateTableRequest;
use Imper86\DynamoDBClient\Message\GetItemRequest;
use Imper86\DynamoDBClient\Model\AttributeValue;
use Imper86\DynamoDBClient\Model\AttributeValueMap;
use Imper86\DynamoDBClient\Model\ReturnConsumedCapacity;
use Imper86\DynamoDBClient\Model\SSESpecification;
use Imper86\DynamoDBClient\Serializer\SerializerFactory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Serializer\Exception\ExceptionInterface;

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
}
