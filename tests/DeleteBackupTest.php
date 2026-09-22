<?php

declare(strict_types=1);

namespace Imper86\DynamoDBClientTests;

use DateTimeImmutable;
use Http\Discovery\Exception\NotFoundException;
use Http\Mock\Client as MockClient;
use Imper86\DynamoDBClient\DynamoDBClient;
use Imper86\DynamoDBClient\Exception\ExceptionInterface;
use Imper86\DynamoDBClient\Exception\MissingCredentialsException;
use Imper86\DynamoDBClient\Exception\ResponseDeserializationException;
use Imper86\DynamoDBClient\Message\DeleteBackupRequest;
use Imper86\DynamoDBClient\Model\BackupDescription;
use Imper86\DynamoDBClient\Model\BackupDetails;
use Imper86\DynamoDBClient\Model\BackupStatus;
use Imper86\DynamoDBClient\Model\BackupType;
use Imper86\DynamoDBClient\Model\BillingMode;
use Imper86\DynamoDBClient\Model\Credentials;
use Imper86\DynamoDBClient\Model\GlobalSecondaryIndexInfo;
use Imper86\DynamoDBClient\Model\KeySchemaElement;
use Imper86\DynamoDBClient\Model\KeyType;
use Imper86\DynamoDBClient\Model\LocalSecondaryIndexInfo;
use Imper86\DynamoDBClient\Model\ProjectionType;
use Imper86\DynamoDBClient\Model\ProvisionedThroughput;
use Imper86\DynamoDBClient\Model\SearchSchemaElement;
use Imper86\DynamoDBClient\Model\SearchSchemaElementType;
use Imper86\DynamoDBClient\Model\SourceTableDetails;
use Imper86\DynamoDBClient\Model\SourceTableFeatureDetails;
use Imper86\DynamoDBClient\Model\SSEDescription;
use Imper86\DynamoDBClient\Model\SSEStatus;
use Imper86\DynamoDBClient\Model\SSEType;
use Imper86\DynamoDBClient\Model\StreamSpecification;
use Imper86\DynamoDBClient\Model\StreamViewType;
use Imper86\DynamoDBClient\Model\TimeToLiveDescription;
use Imper86\DynamoDBClient\Model\TimeToLiveStatus;
use Imper86\DynamoDBClient\Model\VectorAttributeDefinition;
use Imper86\DynamoDBClient\Model\VectorDistanceFunction;
use Imper86\DynamoDBClient\Model\VectorIndexInfo;
use InvalidArgumentException;
use Nyholm\Psr7\Request;
use Nyholm\Psr7\Response;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

use function file_get_contents;
use function str_repeat;

/**
 * The DeleteBackup reference has no Examples section, so the fixtures are built from its request and
 * response syntax, describing a backup of the `Music` table that carried every feature the response
 * can report.
 *
 * @see https://docs.aws.amazon.com/amazondynamodb/latest/APIReference/API_DeleteBackup.html
 * @internal
 */
#[CoversClass(DynamoDBClient::class)]
final class DeleteBackupTest extends TestCase
{
    private const string BACKUP_ARN =
        'arn:aws:dynamodb:eu-central-1:123456789012:table/Music/backup/01576624066799-c3f0dcd7';

    private const string REQUEST_FIXTURE = __DIR__ . '/fixtures/delete-backup-request.json';

    private const string RESPONSE_FIXTURE = __DIR__ . '/fixtures/delete-backup-response.json';

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

        $this->createClient($httpClient)->deleteBackup($this->documentedRequest());

        $sent = $httpClient->getLastRequest();

        self::assertInstanceOf(Request::class, $sent);
        self::assertSame('POST', $sent->getMethod());
        self::assertSame('https://dynamodb.eu-central-1.amazonaws.com/', $sent->getUri()->__toString());
        self::assertSame('DynamoDB_20120810.DeleteBackup', $sent->getHeaderLine('X-Amz-Target'));
        self::assertJsonStringEqualsJsonFile(self::REQUEST_FIXTURE, $sent->getBody()->__toString());
    }

    /**
     * @throws ExceptionInterface
     * @throws InvalidArgumentException
     * @throws MissingCredentialsException
     * @throws NotFoundException
     */
    public function testReturnsTheDetailsOfTheBackupItDeleted(): void
    {
        $httpClient = new MockClient();
        $httpClient->addResponse($this->documentedResponse());

        $response = $this->createClient($httpClient)->deleteBackup($this->documentedRequest());

        $description = $response->backupDescription;

        self::assertInstanceOf(BackupDescription::class, $description);

        $details = $description->backupDetails;

        self::assertInstanceOf(BackupDetails::class, $details);
        self::assertSame(self::BACKUP_ARN, $details->backupArn);
        self::assertSame('MusicBackup', $details->backupName);
        self::assertSame(123456, $details->backupSizeBytes);
        self::assertSame(BackupStatus::DELETED, $details->backupStatus);
        self::assertSame(BackupType::USER, $details->backupType);

        $createdAt = $details->backupCreationDateTime;

        self::assertInstanceOf(DateTimeImmutable::class, $createdAt);
        self::assertSame('2019-12-17T23:07:46.799000+00:00', $createdAt->format('Y-m-d\TH:i:s.uP'));
    }

    /**
     * @throws ExceptionInterface
     * @throws InvalidArgumentException
     * @throws MissingCredentialsException
     * @throws NotFoundException
     */
    public function testReturnsTheTableAsItWasWhenTheBackupWasTaken(): void
    {
        $httpClient = new MockClient();
        $httpClient->addResponse($this->documentedResponse());

        $response = $this->createClient($httpClient)->deleteBackup($this->documentedRequest());

        $description = $response->backupDescription;

        self::assertInstanceOf(BackupDescription::class, $description);

        $source = $description->sourceTableDetails;

        self::assertInstanceOf(SourceTableDetails::class, $source);
        self::assertSame('Music', $source->tableName);
        self::assertSame('a1b2c3d4-5678-90ab-cdef-EXAMPLE11111', $source->tableId);
        self::assertSame('arn:aws:dynamodb:eu-central-1:123456789012:table/Music', $source->tableArn);
        self::assertSame(42, $source->itemCount);
        self::assertSame(123456, $source->tableSizeBytes);
        self::assertSame(BillingMode::PROVISIONED, $source->billingMode);
        self::assertNull($source->onDemandThroughput);

        $throughput = $source->provisionedThroughput;

        self::assertInstanceOf(ProvisionedThroughput::class, $throughput);
        self::assertSame(5, $throughput->readCapacityUnits);
        self::assertSame(5, $throughput->writeCapacityUnits);

        $createdAt = $source->tableCreationDateTime;

        self::assertInstanceOf(DateTimeImmutable::class, $createdAt);
        self::assertSame('2019-12-17T22:50:00.500000+00:00', $createdAt->format('Y-m-d\TH:i:s.uP'));

        $keySchema = $source->keySchema;

        self::assertNotNull($keySchema);
        self::assertCount(2, $keySchema);

        $partitionKey = $keySchema->get(0);

        self::assertInstanceOf(KeySchemaElement::class, $partitionKey);
        self::assertSame('Artist', $partitionKey->attributeName);
        self::assertSame(KeyType::HASH, $partitionKey->keyType);

        $sortKey = $keySchema->get(1);

        self::assertInstanceOf(KeySchemaElement::class, $sortKey);
        self::assertSame('SongTitle', $sortKey->attributeName);
        self::assertSame(KeyType::RANGE, $sortKey->keyType);
    }

    /**
     * @throws ExceptionInterface
     * @throws InvalidArgumentException
     * @throws MissingCredentialsException
     * @throws NotFoundException
     */
    public function testReturnsTheIndexesTheTableHadWhenTheBackupWasTaken(): void
    {
        $features = $this->documentedFeatures();

        $globalIndexes = $features->globalSecondaryIndexes;

        self::assertNotNull($globalIndexes);
        self::assertCount(1, $globalIndexes);

        $globalIndex = $globalIndexes->get(0);

        self::assertInstanceOf(GlobalSecondaryIndexInfo::class, $globalIndex);
        self::assertSame('AlbumTitleIndex', $globalIndex->indexName);
        self::assertNull($globalIndex->onDemandThroughput);
        self::assertCount(1, $globalIndex->keySchema ?? []);

        $globalThroughput = $globalIndex->provisionedThroughput;

        self::assertInstanceOf(ProvisionedThroughput::class, $globalThroughput);
        self::assertSame(10, $globalThroughput->readCapacityUnits);
        self::assertSame(10, $globalThroughput->writeCapacityUnits);

        $globalProjection = $globalIndex->projection;

        self::assertNotNull($globalProjection);
        self::assertSame(ProjectionType::INCLUDE, $globalProjection->projectionType);
        self::assertSame(['Genre'], $globalProjection->nonKeyAttributes?->toArray());

        $localIndexes = $features->localSecondaryIndexes;

        self::assertNotNull($localIndexes);
        self::assertCount(1, $localIndexes);

        $localIndex = $localIndexes->get(0);

        self::assertInstanceOf(LocalSecondaryIndexInfo::class, $localIndex);
        self::assertSame('GenreIndex', $localIndex->indexName);
        self::assertCount(2, $localIndex->keySchema ?? []);

        $localProjection = $localIndex->projection;

        self::assertNotNull($localProjection);
        self::assertSame(ProjectionType::KEYS_ONLY, $localProjection->projectionType);
        self::assertNull($localProjection->nonKeyAttributes);
    }

    /**
     * @throws ExceptionInterface
     * @throws InvalidArgumentException
     * @throws MissingCredentialsException
     * @throws NotFoundException
     */
    public function testReturnsTheVectorIndexesTheTableHadWhenTheBackupWasTaken(): void
    {
        $vectorIndexes = $this->documentedFeatures()->vectorIndexes;

        self::assertNotNull($vectorIndexes);
        self::assertCount(1, $vectorIndexes);

        $vectorIndex = $vectorIndexes->get(0);

        self::assertInstanceOf(VectorIndexInfo::class, $vectorIndex);
        self::assertSame('LyricsEmbeddingIndex', $vectorIndex->indexName);
        self::assertSame(1536, $vectorIndex->dimensions);
        self::assertSame(VectorDistanceFunction::COSINE, $vectorIndex->distanceFunction);
        self::assertSame(ProjectionType::ALL, $vectorIndex->projection?->projectionType);

        $vectorAttribute = $vectorIndex->vectorAttribute;

        self::assertInstanceOf(VectorAttributeDefinition::class, $vectorAttribute);
        self::assertSame('LyricsEmbedding', $vectorAttribute->attributeName);

        $searchSchema = $vectorIndex->searchSchema;

        self::assertNotNull($searchSchema);

        $searchSchemaElement = $searchSchema->get(0);

        self::assertInstanceOf(SearchSchemaElement::class, $searchSchemaElement);
        self::assertSame('Artist', $searchSchemaElement->attributeName);
        self::assertSame(SearchSchemaElementType::HASH, $searchSchemaElement->searchSchemaElementType);
    }

    /**
     * @throws ExceptionInterface
     * @throws InvalidArgumentException
     * @throws MissingCredentialsException
     * @throws NotFoundException
     */
    public function testReturnsTheStreamEncryptionAndTtlSettingsTheTableHadWhenTheBackupWasTaken(): void
    {
        $features = $this->documentedFeatures();

        $stream = $features->streamDescription;

        self::assertInstanceOf(StreamSpecification::class, $stream);
        self::assertTrue($stream->streamEnabled);
        self::assertSame(StreamViewType::NEW_AND_OLD_IMAGES, $stream->streamViewType);

        $encryption = $features->sseDescription;

        self::assertInstanceOf(SSEDescription::class, $encryption);
        self::assertSame(SSEStatus::ENABLED, $encryption->status);
        self::assertSame(SSEType::KMS, $encryption->sseType);
        self::assertSame(
            'arn:aws:kms:eu-central-1:123456789012:key/a1b2c3d4-5678-90ab-cdef-EXAMPLE22222',
            $encryption->kmsMasterKeyArn,
        );
        self::assertNull($encryption->inaccessibleEncryptionDateTime);

        $timeToLive = $features->timeToLiveDescription;

        self::assertInstanceOf(TimeToLiveDescription::class, $timeToLive);
        self::assertSame('ExpiresAt', $timeToLive->attributeName);
        self::assertSame(TimeToLiveStatus::ENABLED, $timeToLive->timeToLiveStatus);
    }

    /**
     * A table with no indexes, no stream and no TTL is described by the backup details alone.
     *
     * @throws ExceptionInterface
     * @throws InvalidArgumentException
     * @throws MissingCredentialsException
     * @throws NotFoundException
     */
    public function testLeavesTheSourceTableElementsNullWhenTheServiceOmitsThem(): void
    {
        $httpClient = new MockClient();
        $httpClient->addResponse(new Response(body: '{"BackupDescription":{"BackupDetails":{'
            . '"BackupArn":"arn:backup","BackupCreationDateTime":1576624066.799,"BackupName":"MusicBackup",'
            . '"BackupStatus":"DELETED","BackupType":"USER"}}}'));

        $response = $this->createClient($httpClient)->deleteBackup($this->documentedRequest());

        $description = $response->backupDescription;

        self::assertInstanceOf(BackupDescription::class, $description);
        self::assertInstanceOf(BackupDetails::class, $description->backupDetails);
        self::assertNull($description->sourceTableDetails);
        self::assertNull($description->sourceTableFeatureDetails);
    }

    /**
     * @throws ExceptionInterface
     * @throws InvalidArgumentException
     * @throws MissingCredentialsException
     * @throws NotFoundException
     */
    public function testLeavesTheFeatureDetailsEmptyWhenTheTableHadNoneOfThem(): void
    {
        $httpClient = new MockClient();
        $httpClient->addResponse(new Response(body: '{"BackupDescription":{"SourceTableFeatureDetails":{}}}'));

        $response = $this->createClient($httpClient)->deleteBackup($this->documentedRequest());

        $features = $response->backupDescription?->sourceTableFeatureDetails;

        self::assertInstanceOf(SourceTableFeatureDetails::class, $features);
        self::assertNull($features->globalSecondaryIndexes);
        self::assertNull($features->localSecondaryIndexes);
        self::assertNull($features->sseDescription);
        self::assertNull($features->streamDescription);
        self::assertNull($features->timeToLiveDescription);
        self::assertNull($features->vectorIndexes);
    }

    /**
     * @throws ExceptionInterface
     * @throws InvalidArgumentException
     * @throws MissingCredentialsException
     * @throws NotFoundException
     */
    public function testLeavesTheBackupDescriptionNullWhenTheServiceOmitsIt(): void
    {
        $httpClient = new MockClient();
        $httpClient->addResponse(new Response(body: '{}'));

        $response = $this->createClient($httpClient)->deleteBackup($this->documentedRequest());

        self::assertNull($response->backupDescription);
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
        $httpClient->addResponse(
            new Response(body: '{"BackupDescription":{"BackupDetails":{"BackupStatus":"RESTORING"}}}'),
        );

        $this->expectException(ResponseDeserializationException::class);

        $this->createClient($httpClient)->deleteBackup($this->documentedRequest());
    }

    /**
     * @throws InvalidArgumentException
     */
    public function testRejectsABackupArnShorterThanThirtySevenCharacters(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new DeleteBackupRequest(backupArn: str_repeat('a', 36));
    }

    /**
     * @throws InvalidArgumentException
     */
    public function testRejectsABackupArnLongerThanTheServiceAllows(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new DeleteBackupRequest(backupArn: str_repeat('a', 1025));
    }

    /**
     * @throws ExceptionInterface
     * @throws InvalidArgumentException
     * @throws MissingCredentialsException
     * @throws NotFoundException
     */
    private function documentedFeatures(): SourceTableFeatureDetails
    {
        $httpClient = new MockClient();
        $httpClient->addResponse($this->documentedResponse());

        $response = $this->createClient($httpClient)->deleteBackup($this->documentedRequest());

        $features = $response->backupDescription?->sourceTableFeatureDetails;

        self::assertInstanceOf(SourceTableFeatureDetails::class, $features);

        return $features;
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
    private function documentedRequest(): DeleteBackupRequest
    {
        return new DeleteBackupRequest(backupArn: self::BACKUP_ARN);
    }

    private function documentedResponse(): Response
    {
        $body = file_get_contents(self::RESPONSE_FIXTURE);

        self::assertIsString($body);

        return new Response(body: $body);
    }
}
