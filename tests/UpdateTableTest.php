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
use Imper86\DynamoDBClient\Message\UpdateTableRequest;
use Imper86\DynamoDBClient\Model\AttributeDefinition;
use Imper86\DynamoDBClient\Model\AttributeDefinitionList;
use Imper86\DynamoDBClient\Model\BillingMode;
use Imper86\DynamoDBClient\Model\Credentials;
use Imper86\DynamoDBClient\Model\GlobalSecondaryIndexUpdate;
use Imper86\DynamoDBClient\Model\GlobalSecondaryIndexUpdateList;
use Imper86\DynamoDBClient\Model\GlobalTableSettingsReplicationMode;
use Imper86\DynamoDBClient\Model\GlobalTableWitnessGroupUpdate;
use Imper86\DynamoDBClient\Model\GlobalTableWitnessGroupUpdateList;
use Imper86\DynamoDBClient\Model\KeySchemaElement;
use Imper86\DynamoDBClient\Model\KeySchemaElementList;
use Imper86\DynamoDBClient\Model\KeyType;
use Imper86\DynamoDBClient\Model\LocalSecondaryIndexDescription;
use Imper86\DynamoDBClient\Model\MultiRegionConsistency;
use Imper86\DynamoDBClient\Model\OnDemandThroughput;
use Imper86\DynamoDBClient\Model\OnDemandThroughputOverride;
use Imper86\DynamoDBClient\Model\Projection;
use Imper86\DynamoDBClient\Model\ProjectionType;
use Imper86\DynamoDBClient\Model\ProvisionedThroughput;
use Imper86\DynamoDBClient\Model\ProvisionedThroughputDescription;
use Imper86\DynamoDBClient\Model\ProvisionedThroughputOverride;
use Imper86\DynamoDBClient\Model\ReplicaGlobalSecondaryIndex;
use Imper86\DynamoDBClient\Model\ReplicaGlobalSecondaryIndexList;
use Imper86\DynamoDBClient\Model\ReplicationGroupUpdate;
use Imper86\DynamoDBClient\Model\ReplicationGroupUpdateList;
use Imper86\DynamoDBClient\Model\ScalarAttributeType;
use Imper86\DynamoDBClient\Model\SearchSchemaElement;
use Imper86\DynamoDBClient\Model\SearchSchemaElementList;
use Imper86\DynamoDBClient\Model\SearchSchemaElementType;
use Imper86\DynamoDBClient\Model\SSESpecification;
use Imper86\DynamoDBClient\Model\SSEType;
use Imper86\DynamoDBClient\Model\StreamSpecification;
use Imper86\DynamoDBClient\Model\StreamViewType;
use Imper86\DynamoDBClient\Model\TableClass;
use Imper86\DynamoDBClient\Model\TableDescription;
use Imper86\DynamoDBClient\Model\TableStatus;
use Imper86\DynamoDBClient\Model\VectorDistanceFunction;
use Imper86\DynamoDBClient\Model\VectorIndexUpdate;
use Imper86\DynamoDBClient\Model\VectorIndexUpdateList;
use Imper86\DynamoDBClient\Model\WarmThroughput;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

use function file_get_contents;
use function str_repeat;

/**
 * The messages exchanged here are the "Modify Provisioned Write Throughput" example of the UpdateTable
 * reference.
 *
 * @see https://docs.aws.amazon.com/amazondynamodb/latest/APIReference/API_UpdateTable.html
 * @internal
 */
#[CoversClass(DynamoDBClient::class)]
final class UpdateTableTest extends TestCase
{
    private const REQUEST_FIXTURE = __DIR__ . '/fixtures/update-table-request.json';

    private const RESPONSE_FIXTURE = __DIR__ . '/fixtures/update-table-response.json';

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

        $this->createClient($httpClient)->updateTable($this->documentedRequest());

        $sent = $httpClient->getLastRequest();

        self::assertInstanceOf(Request::class, $sent);
        self::assertSame('DynamoDB_20120810.UpdateTable', $sent->getHeaderLine('X-Amz-Target'));
        self::assertJsonStringEqualsJsonFile(self::REQUEST_FIXTURE, $sent->getBody()->__toString());
    }

    /**
     * UpdateTable accepts one kind of change per call, so this body would not get past the service; it only
     * shows how every parameter goes on the wire.
     *
     * @throws ExceptionInterface
     * @throws InvalidArgumentException
     * @throws MissingCredentialsException
     * @throws NotFoundException
     */
    public function testSendsEveryOptionalParameter(): void
    {
        $httpClient = new MockClient();
        $httpClient->addResponse(new Response(body: '{}'));

        $this->createClient($httpClient)->updateTable(new UpdateTableRequest(
            tableName: 'Thread',
            attributeDefinitions: new AttributeDefinitionList([
                new AttributeDefinition('LastPostedBy', ScalarAttributeType::STRING),
            ]),
            billingMode: BillingMode::PROVISIONED,
            deletionProtectionEnabled: true,
            globalSecondaryIndexUpdates: new GlobalSecondaryIndexUpdateList([
                GlobalSecondaryIndexUpdate::create(
                    indexName: 'LastPostedByIndex',
                    keySchema: new KeySchemaElementList([new KeySchemaElement('LastPostedBy', KeyType::HASH)]),
                    projection: new Projection(projectionType: ProjectionType::KEYS_ONLY),
                    onDemandThroughput: new OnDemandThroughput(maxReadRequestUnits: 100),
                    provisionedThroughput: new ProvisionedThroughput(5, 5),
                    warmThroughput: new WarmThroughput(readUnitsPerSecond: 12000),
                ),
                GlobalSecondaryIndexUpdate::update(
                    'SubjectIndex',
                    provisionedThroughput: new ProvisionedThroughput(10, 10),
                ),
                GlobalSecondaryIndexUpdate::delete('ObsoleteIndex'),
            ]),
            globalTableSettingsReplicationMode: GlobalTableSettingsReplicationMode::ENABLED,
            globalTableWitnessUpdates: new GlobalTableWitnessGroupUpdateList([
                GlobalTableWitnessGroupUpdate::create('us-east-2'),
            ]),
            multiRegionConsistency: MultiRegionConsistency::STRONG,
            onDemandThroughput: new OnDemandThroughput(maxReadRequestUnits: 200, maxWriteRequestUnits: 100),
            provisionedThroughput: new ProvisionedThroughput(10, 10),
            replicaUpdates: new ReplicationGroupUpdateList([
                ReplicationGroupUpdate::create(
                    regionName: 'eu-west-1',
                    globalSecondaryIndexes: new ReplicaGlobalSecondaryIndexList([
                        new ReplicaGlobalSecondaryIndex(
                            indexName: 'SubjectIndex',
                            onDemandThroughputOverride: new OnDemandThroughputOverride(50),
                            provisionedThroughputOverride: new ProvisionedThroughputOverride(3),
                        ),
                    ]),
                    kmsMasterKeyId: 'alias/replica-key',
                    onDemandThroughputOverride: new OnDemandThroughputOverride(100),
                    provisionedThroughputOverride: new ProvisionedThroughputOverride(7),
                    tableClassOverride: TableClass::STANDARD_INFREQUENT_ACCESS,
                ),
                ReplicationGroupUpdate::update('us-east-1', tableClassOverride: TableClass::STANDARD),
                ReplicationGroupUpdate::delete('ap-south-1'),
            ]),
            sseSpecification: new SSESpecification(enabled: true, sseType: SSEType::KMS),
            streamSpecification: new StreamSpecification(true, StreamViewType::NEW_IMAGE),
            tableClass: TableClass::STANDARD,
            vectorIndexUpdates: new VectorIndexUpdateList([
                VectorIndexUpdate::create(
                    dimensions: 3,
                    distanceFunction: VectorDistanceFunction::COSINE,
                    indexName: 'EmbeddingIndex',
                    projection: new Projection(projectionType: ProjectionType::ALL),
                    vectorAttributeName: 'Embedding',
                    searchSchema: new SearchSchemaElementList([
                        new SearchSchemaElement('ForumName', SearchSchemaElementType::HASH),
                    ]),
                ),
                VectorIndexUpdate::delete('OldEmbeddingIndex'),
            ]),
            warmThroughput: new WarmThroughput(readUnitsPerSecond: 15000, writeUnitsPerSecond: 5000),
        ));

        $sent = $httpClient->getLastRequest();

        self::assertInstanceOf(Request::class, $sent);
        self::assertJsonStringEqualsJsonString(
            '{"TableName":"Thread",'
            . '"AttributeDefinitions":[{"AttributeName":"LastPostedBy","AttributeType":"S"}],'
            . '"BillingMode":"PROVISIONED","DeletionProtectionEnabled":true,'
            . '"GlobalSecondaryIndexUpdates":['
            . '{"Create":{"IndexName":"LastPostedByIndex",'
            . '"KeySchema":[{"AttributeName":"LastPostedBy","KeyType":"HASH"}],'
            . '"Projection":{"ProjectionType":"KEYS_ONLY"},"OnDemandThroughput":{"MaxReadRequestUnits":100},'
            . '"ProvisionedThroughput":{"ReadCapacityUnits":5,"WriteCapacityUnits":5},'
            . '"WarmThroughput":{"ReadUnitsPerSecond":12000}}},'
            . '{"Update":{"IndexName":"SubjectIndex",'
            . '"ProvisionedThroughput":{"ReadCapacityUnits":10,"WriteCapacityUnits":10}}},'
            . '{"Delete":{"IndexName":"ObsoleteIndex"}}],'
            . '"GlobalTableSettingsReplicationMode":"ENABLED",'
            . '"GlobalTableWitnessUpdates":[{"Create":{"RegionName":"us-east-2"}}],'
            . '"MultiRegionConsistency":"STRONG",'
            . '"OnDemandThroughput":{"MaxReadRequestUnits":200,"MaxWriteRequestUnits":100},'
            . '"ProvisionedThroughput":{"ReadCapacityUnits":10,"WriteCapacityUnits":10},'
            . '"ReplicaUpdates":['
            . '{"Create":{"RegionName":"eu-west-1","GlobalSecondaryIndexes":[{"IndexName":"SubjectIndex",'
            . '"OnDemandThroughputOverride":{"MaxReadRequestUnits":50},'
            . '"ProvisionedThroughputOverride":{"ReadCapacityUnits":3}}],'
            . '"KMSMasterKeyId":"alias/replica-key","OnDemandThroughputOverride":{"MaxReadRequestUnits":100},'
            . '"ProvisionedThroughputOverride":{"ReadCapacityUnits":7},'
            . '"TableClassOverride":"STANDARD_INFREQUENT_ACCESS"}},'
            . '{"Update":{"RegionName":"us-east-1","TableClassOverride":"STANDARD"}},'
            . '{"Delete":{"RegionName":"ap-south-1"}}],'
            . '"SSESpecification":{"Enabled":true,"SSEType":"KMS"},'
            . '"StreamSpecification":{"StreamEnabled":true,"StreamViewType":"NEW_IMAGE"},'
            . '"TableClass":"STANDARD",'
            . '"VectorIndexUpdates":['
            . '{"Create":{"Dimensions":3,"DistanceFunction":"COSINE","IndexName":"EmbeddingIndex",'
            . '"Projection":{"ProjectionType":"ALL"},"VectorAttribute":{"AttributeName":"Embedding"},'
            . '"SearchSchema":[{"AttributeName":"ForumName","SearchSchemaElementType":"HASH"}]}},'
            . '{"Delete":{"IndexName":"OldEmbeddingIndex"}}],'
            . '"WarmThroughput":{"ReadUnitsPerSecond":15000,"WriteUnitsPerSecond":5000}}',
            $sent->getBody()->__toString(),
        );
    }

    /**
     * @throws ExceptionInterface
     * @throws InvalidArgumentException
     * @throws MissingCredentialsException
     * @throws NotFoundException
     */
    public function testReturnsTheDescriptionOfTheTableBeingUpdated(): void
    {
        $httpClient = new MockClient();
        $httpClient->addResponse($this->documentedResponse());

        $response = $this->createClient($httpClient)->updateTable($this->documentedRequest());

        $table = $response->tableDescription;

        self::assertInstanceOf(TableDescription::class, $table);
        self::assertSame('arn:aws:dynamodb:us-west-2:123456789012:table/Thread', $table->tableArn);
        self::assertSame('Thread', $table->tableName);
        self::assertSame(TableStatus::UPDATING, $table->tableStatus);
        self::assertSame(0, $table->itemCount);
        self::assertSame(0, $table->tableSizeBytes);
        self::assertCount(3, $table->attributeDefinitions ?? []);
        self::assertCount(2, $table->keySchema ?? []);
        self::assertInstanceOf(DateTimeImmutable::class, $table->creationDateTime);
        self::assertSame('2013-03-20T17:45:28.686000+00:00', $table->creationDateTime->format('Y-m-d\TH:i:s.uP'));

        $index = $table->localSecondaryIndexes?->get(0);

        self::assertInstanceOf(LocalSecondaryIndexDescription::class, $index);
        self::assertSame('LastPostIndex', $index->indexName);
        self::assertSame(ProjectionType::KEYS_ONLY, $index->projection?->projectionType);

        $throughput = $table->provisionedThroughput;

        self::assertInstanceOf(ProvisionedThroughputDescription::class, $throughput);
        self::assertSame(0, $throughput->numberOfDecreasesToday);
        self::assertSame(5, $throughput->readCapacityUnits);
        self::assertSame(5, $throughput->writeCapacityUnits);
        self::assertInstanceOf(DateTimeImmutable::class, $throughput->lastIncreaseDateTime);
        self::assertSame(
            '2013-03-20T17:48:21.282000+00:00',
            $throughput->lastIncreaseDateTime->format('Y-m-d\TH:i:s.uP'),
        );
        self::assertNull($throughput->lastDecreaseDateTime);
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

        $response = $this->createClient($httpClient)->updateTable($this->documentedRequest());

        self::assertNull($response->tableDescription);
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
        $httpClient->addResponse(new Response(body: '{"TableDescription":{"TableStatus":"RESIZING"}}'));

        $this->expectException(ResponseDeserializationException::class);

        $this->createClient($httpClient)->updateTable($this->documentedRequest());
    }

    /**
     * @throws InvalidArgumentException
     */
    public function testRejectsATableNameLongerThanTheServiceAllows(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new UpdateTableRequest(str_repeat('a', 1025));
    }

    /**
     * @throws InvalidArgumentException
     */
    public function testRejectsMoreThanOneWitnessUpdate(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new UpdateTableRequest(
            tableName: 'Thread',
            globalTableWitnessUpdates: new GlobalTableWitnessGroupUpdateList([
                GlobalTableWitnessGroupUpdate::create('us-east-2'),
                GlobalTableWitnessGroupUpdate::delete('us-west-2'),
            ]),
        );
    }

    /**
     * @throws InvalidArgumentException
     */
    public function testRejectsAnEmptyWitnessUpdateList(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new UpdateTableRequest(tableName: 'Thread', globalTableWitnessUpdates: new GlobalTableWitnessGroupUpdateList());
    }

    /**
     * @throws InvalidArgumentException
     */
    public function testRejectsAnEmptyReplicaUpdateList(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new UpdateTableRequest(tableName: 'Thread', replicaUpdates: new ReplicationGroupUpdateList());
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
    private function documentedRequest(): UpdateTableRequest
    {
        return new UpdateTableRequest(tableName: 'Thread', provisionedThroughput: new ProvisionedThroughput(10, 10));
    }

    private function documentedResponse(): Response
    {
        $body = file_get_contents(self::RESPONSE_FIXTURE);

        self::assertIsString($body);

        return new Response(body: $body);
    }
}
