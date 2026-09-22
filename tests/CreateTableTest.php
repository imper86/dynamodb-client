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
use Imper86\DynamoDBClient\Message\CreateTableRequest;
use Imper86\DynamoDBClient\Model\AttributeDefinition;
use Imper86\DynamoDBClient\Model\AttributeDefinitionList;
use Imper86\DynamoDBClient\Model\BillingMode;
use Imper86\DynamoDBClient\Model\Credentials;
use Imper86\DynamoDBClient\Model\GlobalSecondaryIndex;
use Imper86\DynamoDBClient\Model\GlobalSecondaryIndexList;
use Imper86\DynamoDBClient\Model\KeySchemaElement;
use Imper86\DynamoDBClient\Model\KeySchemaElementList;
use Imper86\DynamoDBClient\Model\KeyType;
use Imper86\DynamoDBClient\Model\LocalSecondaryIndex;
use Imper86\DynamoDBClient\Model\LocalSecondaryIndexDescription;
use Imper86\DynamoDBClient\Model\LocalSecondaryIndexDescriptionList;
use Imper86\DynamoDBClient\Model\LocalSecondaryIndexList;
use Imper86\DynamoDBClient\Model\Projection;
use Imper86\DynamoDBClient\Model\ProjectionType;
use Imper86\DynamoDBClient\Model\ProvisionedThroughput;
use Imper86\DynamoDBClient\Model\ScalarAttributeType;
use Imper86\DynamoDBClient\Model\SSEDescription;
use Imper86\DynamoDBClient\Model\SSESpecification;
use Imper86\DynamoDBClient\Model\SSEType;
use Imper86\DynamoDBClient\Model\StreamSpecification;
use Imper86\DynamoDBClient\Model\StreamViewType;
use Imper86\DynamoDBClient\Model\TableDescription;
use Imper86\DynamoDBClient\Model\TableStatus;
use Imper86\DynamoDBClient\Model\Tag;
use Imper86\DynamoDBClient\Model\TagList;
use Imper86\DynamoDBClient\ValueObject\NonEmptyStringList;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

use function file_get_contents;
use function range;
use function str_repeat;

/**
 * The messages exchanged here are the "Create a Table" example of the CreateTable reference.
 *
 * @see https://docs.aws.amazon.com/amazondynamodb/latest/APIReference/API_CreateTable.html
 * @internal
 */
#[CoversClass(DynamoDBClient::class)]
final class CreateTableTest extends TestCase
{
    private const string REQUEST_FIXTURE = __DIR__ . '/fixtures/create-table-request.json';

    private const string RESPONSE_FIXTURE = __DIR__ . '/fixtures/create-table-response.json';

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

        $this->createClient($httpClient)->createTable($this->documentedRequest());

        $sent = $httpClient->getLastRequest();

        self::assertInstanceOf(Request::class, $sent);
        self::assertSame('POST', $sent->getMethod());
        self::assertSame('https://dynamodb.eu-central-1.amazonaws.com/', $sent->getUri()->__toString());
        self::assertSame('DynamoDB_20120810.CreateTable', $sent->getHeaderLine('X-Amz-Target'));
        self::assertJsonStringEqualsJsonFile(self::REQUEST_FIXTURE, $sent->getBody()->__toString());
    }

    /**
     * @throws ExceptionInterface
     * @throws InvalidArgumentException
     * @throws MissingCredentialsException
     * @throws NotFoundException
     */
    public function testReturnsTheDescriptionOfTheTableBeingCreated(): void
    {
        $httpClient = new MockClient();
        $httpClient->addResponse($this->documentedResponse());

        $response = $this->createClient($httpClient)->createTable($this->documentedRequest());

        $table = $response->tableDescription;

        self::assertInstanceOf(TableDescription::class, $table);
        self::assertSame('Thread', $table->tableName);
        self::assertSame('arn:aws:dynamodb:us-west-2:123456789012:table/Thread', $table->tableArn);
        self::assertSame(TableStatus::CREATING, $table->tableStatus);
        self::assertSame(0, $table->itemCount);
        self::assertSame(0, $table->tableSizeBytes);
        self::assertInstanceOf(DateTimeImmutable::class, $table->creationDateTime);
        self::assertSame('2013-03-19T21:21:20.070000+00:00', $table->creationDateTime->format('Y-m-d\TH:i:s.uP'));
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

        $response = $this->createClient($httpClient)->createTable($this->documentedRequest());

        $keySchema = $response->tableDescription?->keySchema;

        self::assertInstanceOf(KeySchemaElementList::class, $keySchema);
        self::assertCount(2, $keySchema);
        self::assertSame('ForumName', $keySchema->get(0)?->attributeName);
        self::assertSame(KeyType::HASH, $keySchema->get(0)?->keyType);
        self::assertSame('Subject', $keySchema->get(1)?->attributeName);
        self::assertSame(KeyType::RANGE, $keySchema->get(1)?->keyType);

        $attributes = $response->tableDescription?->attributeDefinitions;

        self::assertInstanceOf(AttributeDefinitionList::class, $attributes);
        self::assertCount(3, $attributes);
        self::assertSame('ForumName', $attributes->get(0)?->attributeName);
        self::assertSame(ScalarAttributeType::STRING, $attributes->get(0)?->attributeType);
    }

    /**
     * @throws ExceptionInterface
     * @throws InvalidArgumentException
     * @throws MissingCredentialsException
     * @throws NotFoundException
     */
    public function testReturnsTheLocalSecondaryIndexItCreatedAlongWithTheTable(): void
    {
        $httpClient = new MockClient();
        $httpClient->addResponse($this->documentedResponse());

        $response = $this->createClient($httpClient)->createTable($this->documentedRequest());

        $indexes = $response->tableDescription?->localSecondaryIndexes;

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
        self::assertSame(ProjectionType::KEYS_ONLY, $index->projection?->projectionType);

        $indexKeySchema = $index->keySchema;

        self::assertInstanceOf(KeySchemaElementList::class, $indexKeySchema);
        self::assertCount(2, $indexKeySchema);
    }

    /**
     * The service leaves out everything the table does not use, down to the description itself.
     *
     * @throws ExceptionInterface
     * @throws InvalidArgumentException
     * @throws MissingCredentialsException
     * @throws NotFoundException
     */
    public function testLeavesTheElementsTheServiceOmitsNull(): void
    {
        $httpClient = new MockClient();
        $httpClient->addResponse($this->documentedResponse());

        $response = $this->createClient($httpClient)->createTable($this->documentedRequest());

        $table = $response->tableDescription;

        self::assertInstanceOf(TableDescription::class, $table);
        self::assertNull($table->globalSecondaryIndexes);
        self::assertNull($table->billingModeSummary);
        self::assertNull($table->provisionedThroughput);
        self::assertNull($table->sseDescription);
        self::assertNull($table->streamSpecification);
        self::assertNull($table->replicas);
        self::assertNull($table->vectorIndexes);
    }

    /**
     * @throws ExceptionInterface
     * @throws InvalidArgumentException
     * @throws MissingCredentialsException
     * @throws NotFoundException
     */
    public function testLeavesTheTableDescriptionNullWhenTheServiceOmitsIt(): void
    {
        $httpClient = new MockClient();
        $httpClient->addResponse(new Response(body: '{}'));

        $response = $this->createClient($httpClient)->createTable($this->documentedRequest());

        self::assertNull($response->tableDescription);
    }

    /**
     * The `SSE` members are spelled the way AWS spells them, not the way the name converter would.
     *
     * @throws ExceptionInterface
     * @throws InvalidArgumentException
     * @throws MissingCredentialsException
     * @throws NotFoundException
     */
    public function testSpellsTheEncryptionAndStreamMembersTheWayTheApiDoes(): void
    {
        $httpClient = new MockClient();
        $httpClient->addResponse(new Response(body: '{"TableDescription":{"TableName":"Thread",'
            . '"SSEDescription":{"Status":"ENABLED","SSEType":"KMS",'
            . '"KMSMasterKeyArn":"arn:aws:kms:us-west-2:123456789012:key/abcd"}}}'));

        $response = $this->createClient($httpClient)->createTable(new CreateTableRequest(
            tableName: 'Thread',
            sseSpecification: new SSESpecification(
                enabled: true,
                kmsMasterKeyId: 'alias/aws/dynamodb',
                sseType: SSEType::KMS,
            ),
            streamSpecification: new StreamSpecification(
                streamEnabled: true,
                streamViewType: StreamViewType::NEW_AND_OLD_IMAGES,
            ),
        ));

        $sent = $httpClient->getLastRequest();

        self::assertInstanceOf(Request::class, $sent);
        self::assertJsonStringEqualsJsonString(
            '{"TableName":"Thread","SSESpecification":{"Enabled":true,'
            . '"KMSMasterKeyId":"alias/aws/dynamodb","SSEType":"KMS"},'
            . '"StreamSpecification":{"StreamEnabled":true,"StreamViewType":"NEW_AND_OLD_IMAGES"}}',
            $sent->getBody()->__toString(),
        );

        $encryption = $response->tableDescription?->sseDescription;

        self::assertInstanceOf(SSEDescription::class, $encryption);
        self::assertSame(SSEType::KMS, $encryption->sseType);
        self::assertSame('arn:aws:kms:us-west-2:123456789012:key/abcd', $encryption->kmsMasterKeyArn);
    }

    /**
     * @throws ExceptionInterface
     * @throws InvalidArgumentException
     * @throws MissingCredentialsException
     * @throws NotFoundException
     */
    public function testSendsAProvisionedTableWithAGlobalSecondaryIndex(): void
    {
        $httpClient = new MockClient();
        $httpClient->addResponse(new Response(body: '{"TableDescription":{"TableName":"Thread"}}'));

        $this->createClient($httpClient)->createTable(new CreateTableRequest(
            tableName: 'Thread',
            billingMode: BillingMode::PROVISIONED,
            globalSecondaryIndexes: new GlobalSecondaryIndexList([
                new GlobalSecondaryIndex(
                    indexName: 'SubjectIndex',
                    keySchema: new KeySchemaElementList([
                        new KeySchemaElement(attributeName: 'Subject', keyType: KeyType::HASH),
                    ]),
                    projection: new Projection(
                        nonKeyAttributes: new NonEmptyStringList(['LastPostDateTime']),
                        projectionType: ProjectionType::INCLUDE,
                    ),
                    provisionedThroughput: new ProvisionedThroughput(
                        readCapacityUnits: 5,
                        writeCapacityUnits: 1,
                    ),
                ),
            ]),
            provisionedThroughput: new ProvisionedThroughput(readCapacityUnits: 10, writeCapacityUnits: 5),
        ));

        $sent = $httpClient->getLastRequest();

        self::assertInstanceOf(Request::class, $sent);
        self::assertJsonStringEqualsJsonString(
            '{"TableName":"Thread","BillingMode":"PROVISIONED","GlobalSecondaryIndexes":[{'
            . '"IndexName":"SubjectIndex","KeySchema":[{"AttributeName":"Subject","KeyType":"HASH"}],'
            . '"Projection":{"NonKeyAttributes":["LastPostDateTime"],"ProjectionType":"INCLUDE"},'
            . '"ProvisionedThroughput":{"ReadCapacityUnits":5,"WriteCapacityUnits":1}}],'
            . '"ProvisionedThroughput":{"ReadCapacityUnits":10,"WriteCapacityUnits":5}}',
            $sent->getBody()->__toString(),
        );
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
        $httpClient->addResponse(new Response(body: '{"TableDescription":{"TableStatus":"PENDING"}}'));

        $this->expectException(ResponseDeserializationException::class);

        $this->createClient($httpClient)->createTable($this->documentedRequest());
    }

    /**
     * @throws InvalidArgumentException
     */
    public function testRejectsATableNameLongerThanTheServiceAllows(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new CreateTableRequest(tableName: str_repeat('a', 1025));
    }

    /**
     * @throws InvalidArgumentException
     */
    public function testRejectsAnEmptyKeySchema(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new CreateTableRequest(tableName: 'Thread', keySchema: new KeySchemaElementList());
    }

    /**
     * @throws InvalidArgumentException
     */
    public function testRejectsAnIndexNameShorterThanThreeCharacters(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new LocalSecondaryIndex(
            indexName: 'Id',
            keySchema: $this->keySchema(),
            projection: new Projection(projectionType: ProjectionType::ALL),
        );
    }

    /**
     * @throws InvalidArgumentException
     */
    public function testRejectsAnIndexNameWithACharacterThePatternDoesNotAllow(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new LocalSecondaryIndex(
            indexName: 'Last Post Index',
            keySchema: $this->keySchema(),
            projection: new Projection(projectionType: ProjectionType::ALL),
        );
    }

    /**
     * @throws InvalidArgumentException
     */
    public function testRejectsAnIndexWithoutAnyKey(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new GlobalSecondaryIndex(
            indexName: 'LastPostIndex',
            keySchema: new KeySchemaElementList(),
            projection: new Projection(projectionType: ProjectionType::ALL),
        );
    }

    /**
     * @throws InvalidArgumentException
     */
    public function testRejectsMoreProjectedAttributesThanTheServiceAccepts(): void
    {
        $names = [];

        foreach (range(1, 21) as $number) {
            $names[] = "Attribute{$number}";
        }

        $this->expectException(InvalidArgumentException::class);

        new Projection(
            nonKeyAttributes: new NonEmptyStringList($names),
            projectionType: ProjectionType::INCLUDE,
        );
    }

    /**
     * @throws InvalidArgumentException
     */
    public function testRejectsATagKeyLongerThanTheServiceAllows(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new Tag(key: str_repeat('a', 129), value: 'BlueTeam');
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
    private function documentedRequest(): CreateTableRequest
    {
        return new CreateTableRequest(
            tableName: 'Thread',
            attributeDefinitions: new AttributeDefinitionList([
                new AttributeDefinition(attributeName: 'ForumName', attributeType: ScalarAttributeType::STRING),
                new AttributeDefinition(attributeName: 'Subject', attributeType: ScalarAttributeType::STRING),
                new AttributeDefinition(
                    attributeName: 'LastPostDateTime',
                    attributeType: ScalarAttributeType::STRING,
                ),
            ]),
            billingMode: BillingMode::PAY_PER_REQUEST,
            keySchema: $this->keySchema(),
            localSecondaryIndexes: new LocalSecondaryIndexList([
                new LocalSecondaryIndex(
                    indexName: 'LastPostIndex',
                    keySchema: new KeySchemaElementList([
                        new KeySchemaElement(attributeName: 'ForumName', keyType: KeyType::HASH),
                        new KeySchemaElement(attributeName: 'LastPostDateTime', keyType: KeyType::RANGE),
                    ]),
                    projection: new Projection(projectionType: ProjectionType::KEYS_ONLY),
                ),
            ]),
            tags: new TagList([new Tag(key: 'Owner', value: 'BlueTeam')]),
        );
    }

    /**
     * @throws InvalidArgumentException
     */
    private function keySchema(): KeySchemaElementList
    {
        return new KeySchemaElementList([
            new KeySchemaElement(attributeName: 'ForumName', keyType: KeyType::HASH),
            new KeySchemaElement(attributeName: 'Subject', keyType: KeyType::RANGE),
        ]);
    }

    private function documentedResponse(): Response
    {
        $body = file_get_contents(self::RESPONSE_FIXTURE);

        self::assertIsString($body);

        return new Response(body: $body);
    }
}
