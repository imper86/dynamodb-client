<?php

declare(strict_types=1);

namespace OoAws\DynamoDBClientTests\Serializer\Normalizer;

use InvalidArgumentException;
use OoAws\DynamoDBClient\Model\AttributeValue;
use OoAws\DynamoDBClient\Model\AttributeValueObjectList;
use OoAws\DynamoDBClient\Model\AttributeValueObjectMap;
use OoAws\DynamoDBClient\Serializer\Normalizer\CollectionNormalizer;
use OoAws\DynamoDBClient\ValueObject\NonEmptyStringMap;
use OoAws\DynamoDBClient\ValueObject\NumberSet;
use OoAws\DynamoDBClient\ValueObject\StringSet;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Exception\ExceptionInterface;
use Symfony\Component\Serializer\Exception\NotNormalizableValueException;
use Symfony\Component\Serializer\Mapping\Factory\ClassMetadataFactory;
use Symfony\Component\Serializer\Mapping\Loader\AttributeLoader;
use Symfony\Component\Serializer\NameConverter\MetadataAwareNameConverter;
use Symfony\Component\Serializer\Normalizer\AbstractObjectNormalizer;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

/**
 * @internal
 */
#[CoversClass(CollectionNormalizer::class)]
final class CollectionNormalizerTest extends TestCase
{
    private Serializer $serializer;

    protected function setUp(): void
    {
        $metadataFactory = new ClassMetadataFactory(new AttributeLoader());

        $this->serializer = new Serializer(
            [
                new CollectionNormalizer(),
                new ObjectNormalizer($metadataFactory, new MetadataAwareNameConverter($metadataFactory)),
            ],
            [new JsonEncoder()],
        );
    }

    /**
     * @throws ExceptionInterface
     * @throws InvalidArgumentException
     */
    public function testRoundTripOfNestedAttributeValues(): void
    {
        $map = new AttributeValueObjectMap([
            'id' => new AttributeValue(string: 'abc'),
            'tags' => new AttributeValue(stringSet: new StringSet(['a', 'b'])),
            'items' => new AttributeValue(list: new AttributeValueObjectList([
                new AttributeValue(numberSet: new NumberSet(['1', '2.5'])),
                new AttributeValue(map: new AttributeValueObjectMap()),
            ])),
        ]);

        $json = $this->serializer->serialize($map, 'json', [AbstractObjectNormalizer::SKIP_NULL_VALUES => true]);

        self::assertSame('{"id":{"S":"abc"},"tags":{"SS":["a","b"]},"items":{"L":[{"NS":["1","2.5"]},{"M":{}}]}}', $json, 'Empty map must be serialized as a JSON object.');
        $deserialized = $this->serializer->deserialize($json, AttributeValueObjectMap::class, 'json');

        self::assertInstanceOf(AttributeValueObjectMap::class, $deserialized);
        self::assertInstanceOf(StringSet::class, $deserialized->get('tags')?->stringSet);
        self::assertInstanceOf(NumberSet::class, $deserialized->get('items')?->list?->get(0)?->numberSet);
        self::assertSame($json, $this->serializer->serialize($deserialized, 'json', [AbstractObjectNormalizer::SKIP_NULL_VALUES => true]));
    }

    /**
     * @throws ExceptionInterface
     * @throws InvalidArgumentException
     */
    public function testScalarCollections(): void
    {
        self::assertSame('{"#n":"name"}', $this->serializer->serialize(new NonEmptyStringMap(['#n' => 'name']), 'json'));
        $set = $this->serializer->deserialize('["a"]', StringSet::class, 'json');

        self::assertInstanceOf(StringSet::class, $set);
        self::assertSame(['a'], $set->toArray());
    }

    /**
     * @throws ExceptionInterface
     */
    public function testInvalidDataIsReportedAsSerializerException(): void
    {
        $this->expectException(NotNormalizableValueException::class);

        $this->serializer->deserialize('["a","a"]', StringSet::class, 'json');
    }
}
