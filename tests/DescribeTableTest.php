<?php

declare(strict_types=1);

namespace Imper86\DynamoDBClientTests;

use DateTimeImmutable;
use Http\Discovery\Exception\NotFoundException;
use Http\Mock\Client as MockClient;
use InvalidArgumentException;
use Nyholm\Psr7\Request;
use Nyholm\Psr7\Response;
use Imper86\DynamoDBClient\DynamoDBClient;
use Imper86\DynamoDBClient\Exception\ExceptionInterface;
use Imper86\DynamoDBClient\Exception\MissingCredentialsException;
use Imper86\DynamoDBClient\Exception\ResponseDeserializationException;
use Imper86\DynamoDBClient\Message\DescribeTableRequest;
use Imper86\DynamoDBClient\Model\AttributeDefinitionList;
use Imper86\DynamoDBClient\Model\Credentials;
use Imper86\DynamoDBClient\Model\KeySchemaElementList;
use Imper86\DynamoDBClient\Model\KeyType;
use Imper86\DynamoDBClient\Model\LocalSecondaryIndexDescription;
use Imper86\DynamoDBClient\Model\LocalSecondaryIndexDescriptionList;
use Imper86\DynamoDBClient\Model\ProjectionType;
use Imper86\DynamoDBClient\Model\ProvisionedThroughputDescription;
use Imper86\DynamoDBClient\Model\ScalarAttributeType;
use Imper86\DynamoDBClient\Model\TableDescription;
use Imper86\DynamoDBClient\Model\TableStatus;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

use function file_get_contents;
use function str_repeat;

/**
 * The messages exchanged here are the "Describe a Table" example of the DescribeTable reference.
 *
 * @see https://docs.aws.amazon.com/amazondynamodb/latest/APIReference/API_DescribeTable.html
 * @internal
 */
#[CoversClass(DynamoDBClient::class)]
final class DescribeTableTest extends TestCase
{
    private const REQUEST_FIXTURE = __DIR__ . '/fixtures/describe-table-request.json';

    private const RESPONSE_FIXTURE = __DIR__ . '/fixtures/describe-table-response.json';

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

        $this->createClient($httpClient)->describeTable(new DescribeTableRequest('Thread'));

        $sent = $httpClient->getLastRequest();

        self::assertInstanceOf(Request::class, $sent);
        self::assertSame('DynamoDB_20120810.DescribeTable', $sent->getHeaderLine('X-Amz-Target'));
        self::assertJsonStringEqualsJsonFile(self::REQUEST_FIXTURE, $sent->getBody()->__toString());
    }

    /**
     * @throws ExceptionInterface
     * @throws InvalidArgumentException
     * @throws MissingCredentialsException
     * @throws NotFoundException
     */
    public function testReturnsTheDescriptionOfTheTable(): void
    {
        $httpClient = new MockClient();
        $httpClient->addResponse($this->documentedResponse());

        $response = $this->createClient($httpClient)->describeTable(new DescribeTableRequest('Thread'));

        $table = $response->table;

        self::assertInstanceOf(TableDescription::class, $table);
        self::assertSame('Thread', $table->tableName);
        self::assertSame('arn:aws:dynamodb:us-west-2:123456789012:table/Thread', $table->tableArn);
        self::assertSame(TableStatus::ACTIVE, $table->tableStatus);
        self::assertSame(0, $table->itemCount);
        self::assertSame(0, $table->tableSizeBytes);
        self::assertInstanceOf(DateTimeImmutable::class, $table->creationDateTime);
        self::assertSame('2013-03-19T21:36:42.358000+00:00', $table->creationDateTime->format('Y-m-d\TH:i:s.uP'));

        $throughput = $table->provisionedThroughput;

        self::assertInstanceOf(ProvisionedThroughputDescription::class, $throughput);
        self::assertSame(0, $throughput->numberOfDecreasesToday);
        self::assertSame(5, $throughput->readCapacityUnits);
        self::assertSame(5, $throughput->writeCapacityUnits);
    }

    /**
     * @throws ExceptionInterface
     * @throws InvalidArgumentException
     * @throws MissingCredentialsException
     * @throws NotFoundException
     */
    public function testReturnsTheKeySchemaAndAttributesOfTheTable(): void
    {
        $httpClient = new MockClient();
        $httpClient->addResponse($this->documentedResponse());

        $response = $this->createClient($httpClient)->describeTable(new DescribeTableRequest('Thread'));

        $keySchema = $response->table?->keySchema;

        self::assertInstanceOf(KeySchemaElementList::class, $keySchema);
        self::assertCount(2, $keySchema);
        self::assertSame('ForumName', $keySchema->get(0)?->attributeName);
        self::assertSame(KeyType::HASH, $keySchema->get(0)?->keyType);
        self::assertSame('Subject', $keySchema->get(1)?->attributeName);
        self::assertSame(KeyType::RANGE, $keySchema->get(1)?->keyType);

        $attributes = $response->table?->attributeDefinitions;

        self::assertInstanceOf(AttributeDefinitionList::class, $attributes);
        self::assertCount(3, $attributes);
        self::assertSame('LastPostDateTime', $attributes->get(1)?->attributeName);
        self::assertSame(ScalarAttributeType::STRING, $attributes->get(1)?->attributeType);
    }

    /**
     * @throws ExceptionInterface
     * @throws InvalidArgumentException
     * @throws MissingCredentialsException
     * @throws NotFoundException
     */
    public function testReturnsTheLocalSecondaryIndexesOfTheTable(): void
    {
        $httpClient = new MockClient();
        $httpClient->addResponse($this->documentedResponse());

        $response = $this->createClient($httpClient)->describeTable(new DescribeTableRequest('Thread'));

        $indexes = $response->table?->localSecondaryIndexes;

        self::assertInstanceOf(LocalSecondaryIndexDescriptionList::class, $indexes);
        self::assertCount(1, $indexes);

        $index = $indexes->get(0);

        self::assertInstanceOf(LocalSecondaryIndexDescription::class, $index);
        self::assertSame('LastPostIndex', $index->indexName);
        self::assertSame(
            'arn:aws:dynamodb:us-west-2:123456789012:table/Thread/index/LastPostIndex',
            $index->indexArn,
        );
        self::assertSame(0, $index->indexSizeBytes);
        self::assertSame(0, $index->itemCount);
        self::assertSame(ProjectionType::KEYS_ONLY, $index->projection?->projectionType);

        $indexKeySchema = $index->keySchema;

        self::assertInstanceOf(KeySchemaElementList::class, $indexKeySchema);
        self::assertCount(2, $indexKeySchema);
        self::assertSame('LastPostDateTime', $indexKeySchema->get(1)?->attributeName);
    }

    /**
     * @throws ExceptionInterface
     * @throws InvalidArgumentException
     * @throws MissingCredentialsException
     * @throws NotFoundException
     */
    public function testLeavesTheElementsTheServiceOmitsNull(): void
    {
        $httpClient = new MockClient();
        $httpClient->addResponse($this->documentedResponse());

        $response = $this->createClient($httpClient)->describeTable(new DescribeTableRequest('Thread'));

        $table = $response->table;

        self::assertInstanceOf(TableDescription::class, $table);
        self::assertNull($table->globalSecondaryIndexes);
        self::assertNull($table->billingModeSummary);
        self::assertNull($table->streamSpecification);
        self::assertNull($table->replicas);
    }

    /**
     * @throws ExceptionInterface
     * @throws InvalidArgumentException
     * @throws MissingCredentialsException
     * @throws NotFoundException
     */
    public function testLeavesTheTableNullWhenTheServiceOmitsIt(): void
    {
        $httpClient = new MockClient();
        $httpClient->addResponse(new Response(body: '{}'));

        $response = $this->createClient($httpClient)->describeTable(new DescribeTableRequest('Thread'));

        self::assertNull($response->table);
    }

    /**
     * @throws ExceptionInterface
     * @throws InvalidArgumentException
     * @throws MissingCredentialsException
     * @throws NotFoundException
     */
    public function testFailsOnABodyItCannotDeserialize(): void
    {
        $httpClient = new MockClient();
        $httpClient->addResponse(new Response(body: '{"Table":{"TableStatus":"GONE"}}'));

        $this->expectException(ResponseDeserializationException::class);

        $this->createClient($httpClient)->describeTable(new DescribeTableRequest('Thread'));
    }

    /**
     * @throws InvalidArgumentException
     */
    public function testRejectsATableNameLongerThanTheServiceAllows(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new DescribeTableRequest(str_repeat('a', 1025));
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

    private function documentedResponse(): Response
    {
        $body = file_get_contents(self::RESPONSE_FIXTURE);

        self::assertIsString($body);

        return new Response(body: $body);
    }
}
