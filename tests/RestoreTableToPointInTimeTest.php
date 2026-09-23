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
use Imper86\DynamoDBClient\Message\RestoreTableToPointInTimeRequest;
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
use Imper86\DynamoDBClient\Model\WarmThroughput;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

use function file_get_contents;
use function str_repeat;

/**
 * The RestoreTableToPointInTime reference has no Examples section, so the fixtures are built from its
 * request and response syntax, with the values a restore of the `Music` table to its latest restorable
 * time would come back with.
 *
 * @see https://docs.aws.amazon.com/amazondynamodb/latest/APIReference/API_RestoreTableToPointInTime.html
 * @internal
 */
#[CoversClass(DynamoDBClient::class)]
final class RestoreTableToPointInTimeTest extends TestCase
{
    private const string REQUEST_FIXTURE = __DIR__ . '/fixtures/restore-table-to-point-in-time-request.json';

    private const string RESPONSE_FIXTURE = __DIR__ . '/fixtures/restore-table-to-point-in-time-response.json';

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

        $this->createClient($httpClient)->restoreTableToPointInTime($this->documentedRequest());

        $sent = $httpClient->getLastRequest();

        self::assertInstanceOf(Request::class, $sent);
        self::assertSame('DynamoDB_20120810.RestoreTableToPointInTime', $sent->getHeaderLine('X-Amz-Target'));
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

        $this->createClient($httpClient)->restoreTableToPointInTime(new RestoreTableToPointInTimeRequest(
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
            restoreDateTime: new DateTimeImmutable('@1576624066.799'),
            sourceTableArn: 'arn:aws:dynamodb:eu-central-1:123456789012:table/Music',
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
            '{"TargetTableName":"MusicRestored","BillingModeOverride":"PROVISIONED",'
            . '"GlobalSecondaryIndexOverride":[{"IndexName":"AlbumIndex",'
            . '"KeySchema":[{"AttributeName":"AlbumTitle","KeyType":"HASH"}],'
            . '"Projection":{"ProjectionType":"KEYS_ONLY"},'
            . '"ProvisionedThroughput":{"ReadCapacityUnits":5,"WriteCapacityUnits":5}}],'
            . '"LocalSecondaryIndexOverride":[{"IndexName":"YearIndex",'
            . '"KeySchema":[{"AttributeName":"Artist","KeyType":"HASH"},{"AttributeName":"Year","KeyType":"RANGE"}],'
            . '"Projection":{"ProjectionType":"ALL"}}],'
            . '"OnDemandThroughputOverride":{"MaxReadRequestUnits":100,"MaxWriteRequestUnits":50},'
            . '"ProvisionedThroughputOverride":{"ReadCapacityUnits":10,"WriteCapacityUnits":5},'
            . '"RestoreDateTime":1576624066.799,'
            . '"SourceTableArn":"arn:aws:dynamodb:eu-central-1:123456789012:table/Music",'
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

        $response = $this->createClient($httpClient)->restoreTableToPointInTime($this->documentedRequest());

        $table = $response->tableDescription;

        self::assertInstanceOf(TableDescription::class, $table);
        self::assertSame('arn:aws:dynamodb:eu-central-1:123456789012:table/MusicRestored', $table->tableArn);
        self::assertSame('MusicRestored', $table->tableName);
        self::assertSame(TableStatus::CREATING, $table->tableStatus);
        self::assertCount(2, $table->keySchema ?? []);
    }

    /**
     * A point-in-time restore has no backup, so the summary names only the source table.
     *
     * @throws ExceptionInterface
     * @throws InvalidArgumentException
     * @throws MissingCredentialsException
     * @throws NotFoundException
     */
    public function testReturnsTheSummaryOfTheRestore(): void
    {
        $httpClient = new MockClient();
        $httpClient->addResponse($this->documentedResponse());

        $response = $this->createClient($httpClient)->restoreTableToPointInTime($this->documentedRequest());

        $summary = $response->tableDescription?->restoreSummary;

        self::assertInstanceOf(RestoreSummary::class, $summary);
        self::assertTrue($summary->restoreInProgress);
        self::assertNull($summary->sourceBackupArn);
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

        $response = $this->createClient($httpClient)->restoreTableToPointInTime($this->documentedRequest());

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

        $this->createClient($httpClient)->restoreTableToPointInTime($this->documentedRequest());
    }

    /**
     * @throws InvalidArgumentException
     */
    public function testRejectsATargetTableNameShorterThanThreeCharacters(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new RestoreTableToPointInTimeRequest(targetTableName: 'Mu', sourceTableName: 'Music');
    }

    /**
     * @throws InvalidArgumentException
     */
    public function testRejectsATargetTableNameLongerThanTwoHundredAndFiftyFiveCharacters(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new RestoreTableToPointInTimeRequest(targetTableName: str_repeat('a', 256), sourceTableName: 'Music');
    }

    /**
     * A table ARN is not a valid target: the restore creates the table, so only a name will do.
     *
     * @throws InvalidArgumentException
     */
    public function testRejectsATargetTableNameWithACharacterThePatternDoesNotAllow(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new RestoreTableToPointInTimeRequest(
            targetTableName: 'arn:aws:dynamodb:eu-central-1:123456789012:table/MusicRestored',
            sourceTableName: 'Music',
        );
    }

    /**
     * @throws InvalidArgumentException
     */
    public function testRejectsASourceTableArnLongerThanTheServiceAllows(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new RestoreTableToPointInTimeRequest(targetTableName: 'MusicRestored', sourceTableArn: str_repeat('a', 1025));
    }

    /**
     * @throws InvalidArgumentException
     */
    public function testRejectsASourceTableNameShorterThanThreeCharacters(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new RestoreTableToPointInTimeRequest(targetTableName: 'MusicRestored', sourceTableName: 'Mu');
    }

    /**
     * @throws InvalidArgumentException
     */
    public function testRejectsASourceTableNameLongerThanTwoHundredAndFiftyFiveCharacters(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new RestoreTableToPointInTimeRequest(targetTableName: 'MusicRestored', sourceTableName: str_repeat('a', 256));
    }

    /**
     * @throws InvalidArgumentException
     */
    public function testRejectsASourceTableNameWithACharacterThePatternDoesNotAllow(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new RestoreTableToPointInTimeRequest(targetTableName: 'MusicRestored', sourceTableName: 'Music Table');
    }

    /**
     * The reference warns that the service fails the request when a global secondary index override
     * carries a warm throughput, although the shared index model has one.
     *
     * @throws InvalidArgumentException
     */
    public function testRejectsAGlobalSecondaryIndexOverrideWithAWarmThroughput(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new RestoreTableToPointInTimeRequest(
            targetTableName: 'MusicRestored',
            globalSecondaryIndexOverride: new GlobalSecondaryIndexList([
                new GlobalSecondaryIndex(
                    indexName: 'AlbumIndex',
                    keySchema: new KeySchemaElementList([
                        new KeySchemaElement(attributeName: 'AlbumTitle', keyType: KeyType::HASH),
                    ]),
                    projection: new Projection(projectionType: ProjectionType::KEYS_ONLY),
                    warmThroughput: new WarmThroughput(readUnitsPerSecond: 12000, writeUnitsPerSecond: 4000),
                ),
            ]),
            sourceTableName: 'Music',
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
    private function documentedRequest(): RestoreTableToPointInTimeRequest
    {
        return new RestoreTableToPointInTimeRequest(
            targetTableName: 'MusicRestored',
            sourceTableName: 'Music',
            useLatestRestorableTime: true,
        );
    }

    private function documentedResponse(): Response
    {
        $body = file_get_contents(self::RESPONSE_FIXTURE);

        self::assertIsString($body);

        return new Response(body: $body);
    }
}
