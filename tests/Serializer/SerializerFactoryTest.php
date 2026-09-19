<?php

declare(strict_types=1);

namespace OoAws\DynamoDBClientTests\Serializer;

use InvalidArgumentException;
use OoAws\DynamoDBClient\Message\GetItemRequest;
use OoAws\DynamoDBClient\Model\AttributeValue;
use OoAws\DynamoDBClient\Model\AttributeValueMap;
use OoAws\DynamoDBClient\Model\ReturnConsumedCapacity;
use OoAws\DynamoDBClient\Serializer\SerializerFactory;
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
}
