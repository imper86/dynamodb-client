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
use Imper86\DynamoDBClient\Message\RestoreTableFromBackupRequest;
use Imper86\DynamoDBClient\Model\BillingMode;
use Imper86\DynamoDBClient\Model\Credentials;
use Imper86\DynamoDBClient\Model\GlobalSecondaryIndex;
use Imper86\DynamoDBClient\Model\GlobalSecondaryIndexList;
use Imper86\DynamoDBClient\Model\KeySchemaElement;
use Imper86\DynamoDBClient\Model\KeySchemaElementList;
use Imper86\DynamoDBClient\Model\KeyType;
use Imper86\DynamoDBClient\Model\LocalSecondaryIndex;
use Imper86\DynamoDBClient\Model\LocalSecondaryIndexList;
use Imper86\DynamoDBClient\Model\OnDemandThroughput;
use Imper86\DynamoDBClient\Model\Projection;
use Imper86\DynamoDBClient\Model\ProjectionType;
use Imper86\DynamoDBClient\Model\ProvisionedThroughput;
use Imper86\DynamoDBClient\Model\RestoreSummary;
use Imper86\DynamoDBClient\Model\SSESpecification;
use Imper86\DynamoDBClient\Model\SSEType;
use Imper86\DynamoDBClient\Model\TableDescription;
use Imper86\DynamoDBClient\Model\TableStatus;
use Imper86\DynamoDBClient\Model\VectorAttributeDefinition;
use Imper86\DynamoDBClient\Model\VectorDistanceFunction;
use Imper86\DynamoDBClient\Model\VectorIndex;
use Imper86\DynamoDBClient\Model\VectorIndexList;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

use function file_get_contents;
use function str_repeat;

/**
 * The RestoreTableFromBackup reference has no Examples section, so the fixtures are built from its request
 * and response syntax, with the values a restore of the `Music` backup into a new table would come back
 * with.
 *
 * @see https://docs.aws.amazon.com/amazondynamodb/latest/APIReference/API_RestoreTableFromBackup.html
 * @internal
 */
#[CoversClass(DynamoDBClient::class)]
final class RestoreTableFromBackupTest extends TestCase
{
    private const string REQUEST_FIXTURE = __DIR__ . '/fixtures/restore-table-from-backup-request.json';

    private const string RESPONSE_FIXTURE = __DIR__ . '/fixtures/restore-table-from-backup-response.json';

    private const string BACKUP_ARN = 'arn:aws:dynamodb:eu-central-1:123456789012:table/Music/backup/01576624066799-c3f0dcd7';

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

        $this->createClient($httpClient)->restoreTableFromBackup($this->documentedRequest());

        $sent = $httpClient->getLastRequest();

        self::assertInstanceOf(Request::class, $sent);
        self::assertSame('DynamoDB_20120810.RestoreTableFromBackup', $sent->getHeaderLine('X-Amz-Target'));
        self::assertJsonStringEqualsJsonFile(self::REQUEST_FIXTURE, $sent->getBody()->__toString());
    }

    /**
     * @throws ExceptionInterface
     * @throws InvalidArgumentException
     * @throws MissingCredentialsException
     * @throws NotFoundException
     */
    public function testSendsEveryOverride(): void
    {
        $httpClient = new MockClient();
        $httpClient->addResponse(new Response(body: '{}'));

        $this->createClient($httpClient)->restoreTableFromBackup(new RestoreTableFromBackupRequest(
            backupArn: self::BACKUP_ARN,
            targetTableName: 'MusicRestored',
            billingModeOverride: BillingMode::PROVISIONED,
            globalSecondaryIndexOverride: new GlobalSecondaryIndexList([
                new GlobalSecondaryIndex(
                    indexName: 'AlbumIndex',
                    keySchema: new KeySchemaElementList([
                        new KeySchemaElement(attributeName: 'AlbumTitle', keyType: KeyType::HASH),
                    ]),
                    projection: new Projection(projectionType: ProjectionType::KEYS_ONLY),
                    provisionedThroughput: new ProvisionedThroughput(readCapacityUnits: 5, writeCapacityUnits: 5),
                ),
            ]),
            localSecondaryIndexOverride: new LocalSecondaryIndexList([
                new LocalSecondaryIndex(
                    indexName: 'YearIndex',
                    keySchema: new KeySchemaElementList([
                        new KeySchemaElement(attributeName: 'Artist', keyType: KeyType::HASH),
                        new KeySchemaElement(attributeName: 'Year', keyType: KeyType::RANGE),
                    ]),
                    projection: new Projection(projectionType: ProjectionType::ALL),
                ),
            ]),
            onDemandThroughputOverride: new OnDemandThroughput(maxReadRequestUnits: 100, maxWriteRequestUnits: 50),
            provisionedThroughputOverride: new ProvisionedThroughput(readCapacityUnits: 10, writeCapacityUnits: 5),
            sseSpecificationOverride: new SSESpecification(
                enabled: true,
                kmsMasterKeyId: 'alias/aws/dynamodb',
                sseType: SSEType::KMS,
            ),
            vectorIndexOverride: new VectorIndexList([
                new VectorIndex(
                    dimensions: 3,
                    distanceFunction: VectorDistanceFunction::COSINE,
                    indexName: 'LyricsIndex',
                    projection: new Projection(projectionType: ProjectionType::KEYS_ONLY),
                    vectorAttribute: new VectorAttributeDefinition('LyricsEmbedding'),
                ),
            ]),
        ));

        $sent = $httpClient->getLastRequest();

        self::assertInstanceOf(Request::class, $sent);
        self::assertJsonStringEqualsJsonString(
            '{"BackupArn":"' . self::BACKUP_ARN . '","TargetTableName":"MusicRestored",'
            . '"BillingModeOverride":"PROVISIONED",'
            . '"GlobalSecondaryIndexOverride":[{"IndexName":"AlbumIndex",'
            . '"KeySchema":[{"AttributeName":"AlbumTitle","KeyType":"HASH"}],'
            . '"Projection":{"ProjectionType":"KEYS_ONLY"},'
            . '"ProvisionedThroughput":{"ReadCapacityUnits":5,"WriteCapacityUnits":5}}],'
            . '"LocalSecondaryIndexOverride":[{"IndexName":"YearIndex",'
            . '"KeySchema":[{"AttributeName":"Artist","KeyType":"HASH"},{"AttributeName":"Year","KeyType":"RANGE"}],'
            . '"Projection":{"ProjectionType":"ALL"}}],'
            . '"OnDemandThroughputOverride":{"MaxReadRequestUnits":100,"MaxWriteRequestUnits":50},'
            . '"ProvisionedThroughputOverride":{"ReadCapacityUnits":10,"WriteCapacityUnits":5},'
            . '"SSESpecificationOverride":{"Enabled":true,"KMSMasterKeyId":"alias/aws/dynamodb","SSEType":"KMS"},'
            . '"VectorIndexOverride":[{"Dimensions":3,"DistanceFunction":"COSINE","IndexName":"LyricsIndex",'
            . '"Projection":{"ProjectionType":"KEYS_ONLY"},"VectorAttribute":{"AttributeName":"LyricsEmbedding"}}]}',
            $sent->getBody()->__toString(),
        );
    }

    /**
     * @throws ExceptionInterface
     * @throws InvalidArgumentException
     * @throws MissingCredentialsException
     * @throws NotFoundException
     */
    public function testReturnsTheDescriptionOfTheTableBeingRestored(): void
    {
        $httpClient = new MockClient();
        $httpClient->addResponse($this->documentedResponse());

        $response = $this->createClient($httpClient)->restoreTableFromBackup($this->documentedRequest());

        $table = $response->tableDescription;

        self::assertInstanceOf(TableDescription::class, $table);
        self::assertSame('arn:aws:dynamodb:eu-central-1:123456789012:table/MusicRestored', $table->tableArn);
        self::assertSame('MusicRestored', $table->tableName);
        self::assertSame(TableStatus::CREATING, $table->tableStatus);
        self::assertCount(2, $table->keySchema ?? []);
    }

    /**
     * @throws ExceptionInterface
     * @throws InvalidArgumentException
     * @throws MissingCredentialsException
     * @throws NotFoundException
     */
    public function testReturnsTheSummaryOfTheRestore(): void
    {
        $httpClient = new MockClient();
        $httpClient->addResponse($this->documentedResponse());

        $response = $this->createClient($httpClient)->restoreTableFromBackup($this->documentedRequest());

        $summary = $response->tableDescription?->restoreSummary;

        self::assertInstanceOf(RestoreSummary::class, $summary);
        self::assertTrue($summary->restoreInProgress);
        self::assertSame(self::BACKUP_ARN, $summary->sourceBackupArn);
        self::assertSame('arn:aws:dynamodb:eu-central-1:123456789012:table/Music', $summary->sourceTableArn);
        self::assertInstanceOf(DateTimeImmutable::class, $summary->restoreDateTime);
        self::assertSame('2019-12-17T23:07:46.799000+00:00', $summary->restoreDateTime->format('Y-m-d\TH:i:s.uP'));
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

        $response = $this->createClient($httpClient)->restoreTableFromBackup($this->documentedRequest());

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
        $httpClient->addResponse(new Response(body: '{"TableDescription":{"RestoreSummary":"in progress"}}'));

        $this->expectException(ResponseDeserializationException::class);

        $this->createClient($httpClient)->restoreTableFromBackup($this->documentedRequest());
    }

    /**
     * @throws InvalidArgumentException
     */
    public function testRejectsABackupArnShorterThanThirtySevenCharacters(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new RestoreTableFromBackupRequest(backupArn: str_repeat('a', 36), targetTableName: 'MusicRestored');
    }

    /**
     * @throws InvalidArgumentException
     */
    public function testRejectsABackupArnLongerThanTheServiceAllows(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new RestoreTableFromBackupRequest(backupArn: str_repeat('a', 1025), targetTableName: 'MusicRestored');
    }

    /**
     * @throws InvalidArgumentException
     */
    public function testRejectsATargetTableNameShorterThanThreeCharacters(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new RestoreTableFromBackupRequest(backupArn: self::BACKUP_ARN, targetTableName: 'Mu');
    }

    /**
     * @throws InvalidArgumentException
     */
    public function testRejectsATargetTableNameLongerThanTwoHundredAndFiftyFiveCharacters(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new RestoreTableFromBackupRequest(backupArn: self::BACKUP_ARN, targetTableName: str_repeat('a', 256));
    }

    /**
     * A table ARN is not a valid target: the restore creates the table, so only a name will do.
     *
     * @throws InvalidArgumentException
     */
    public function testRejectsATargetTableNameWithACharacterThePatternDoesNotAllow(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new RestoreTableFromBackupRequest(
            backupArn: self::BACKUP_ARN,
            targetTableName: 'arn:aws:dynamodb:eu-central-1:123456789012:table/MusicRestored',
        );
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
    private function documentedRequest(): RestoreTableFromBackupRequest
    {
        return new RestoreTableFromBackupRequest(backupArn: self::BACKUP_ARN, targetTableName: 'MusicRestored');
    }

    private function documentedResponse(): Response
    {
        $body = file_get_contents(self::RESPONSE_FIXTURE);

        self::assertIsString($body);

        return new Response(body: $body);
    }
}
