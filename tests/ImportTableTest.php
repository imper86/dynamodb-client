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
use Imper86\DynamoDBClient\Message\ImportTableRequest;
use Imper86\DynamoDBClient\Model\AttributeDefinition;
use Imper86\DynamoDBClient\Model\AttributeDefinitionList;
use Imper86\DynamoDBClient\Model\BillingMode;
use Imper86\DynamoDBClient\Model\Credentials;
use Imper86\DynamoDBClient\Model\GlobalSecondaryIndex;
use Imper86\DynamoDBClient\Model\GlobalSecondaryIndexList;
use Imper86\DynamoDBClient\Model\ImportStatus;
use Imper86\DynamoDBClient\Model\ImportTableDescription;
use Imper86\DynamoDBClient\Model\InputCompressionType;
use Imper86\DynamoDBClient\Model\InputFormat;
use Imper86\DynamoDBClient\Model\InputFormatOptions;
use Imper86\DynamoDBClient\Model\KeySchemaElement;
use Imper86\DynamoDBClient\Model\KeySchemaElementList;
use Imper86\DynamoDBClient\Model\KeyType;
use Imper86\DynamoDBClient\Model\Projection;
use Imper86\DynamoDBClient\Model\ProjectionType;
use Imper86\DynamoDBClient\Model\S3BucketSource;
use Imper86\DynamoDBClient\Model\ScalarAttributeType;
use Imper86\DynamoDBClient\Model\SSESpecification;
use Imper86\DynamoDBClient\Model\SSEType;
use Imper86\DynamoDBClient\Model\TableCreationParameters;
use InvalidArgumentException;
use JsonException;
use Nyholm\Psr7\Request;
use Nyholm\Psr7\Response;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

use function file_get_contents;
use function json_decode;

use const JSON_THROW_ON_ERROR;

/**
 * The ImportTable reference has no Examples section, so the fixtures are built from its request and
 * response syntax: the import of gzipped, semicolon-separated CSV files into a new on-demand `Music`
 * table that DescribeImportTest describes once it has completed, answered here while it is in progress.
 *
 * The rules of `TableCreationParameters`, `S3BucketSource` and `CsvOptions` are tested in
 * DescribeImportTest, where those models first arrived.
 *
 * @see https://docs.aws.amazon.com/amazondynamodb/latest/APIReference/API_ImportTable.html
 * @internal
 */
#[CoversClass(DynamoDBClient::class)]
final class ImportTableTest extends TestCase
{
    private const string REQUEST_FIXTURE = __DIR__ . '/fixtures/import-table-request.json';

    private const string RESPONSE_FIXTURE = __DIR__ . '/fixtures/import-table-response.json';

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

        $this->createClient($httpClient)->importTable($this->documentedRequest());

        $sent = $httpClient->getLastRequest();

        self::assertInstanceOf(Request::class, $sent);
        self::assertSame('DynamoDB_20120810.ImportTable', $sent->getHeaderLine('X-Amz-Target'));
        self::assertJsonStringEqualsJsonFile(self::REQUEST_FIXTURE, $sent->getBody()->__toString());
    }

    /**
     * @throws ExceptionInterface
     * @throws InvalidArgumentException
     * @throws MissingCredentialsException
     * @throws NotFoundException
     * @throws JsonException
     */
    public function testSendsOnlyTheRequiredParameters(): void
    {
        $httpClient = new MockClient();
        $httpClient->addResponse($this->documentedResponse());

        $this->createClient($httpClient)->importTable(new ImportTableRequest(
            inputFormat: InputFormat::DYNAMODB_JSON,
            s3BucketSource: new S3BucketSource('music-imports'),
            tableCreationParameters: new TableCreationParameters(
                attributeDefinitions: new AttributeDefinitionList([
                    new AttributeDefinition('Artist', ScalarAttributeType::STRING),
                ]),
                keySchema: new KeySchemaElementList([new KeySchemaElement('Artist', KeyType::HASH)]),
                tableName: 'Music',
            ),
        ));

        $sent = $httpClient->getLastRequest();

        self::assertInstanceOf(Request::class, $sent);
        self::assertSame(
            [
                'InputFormat' => 'DYNAMODB_JSON',
                'S3BucketSource' => ['S3Bucket' => 'music-imports'],
                'TableCreationParameters' => [
                    'AttributeDefinitions' => [['AttributeName' => 'Artist', 'AttributeType' => 'S']],
                    'KeySchema' => [['AttributeName' => 'Artist', 'KeyType' => 'HASH']],
                    'TableName' => 'Music',
                ],
            ],
            json_decode($sent->getBody()->__toString(), true, flags: JSON_THROW_ON_ERROR),
        );
    }

    /**
     * @throws ExceptionInterface
     * @throws InvalidArgumentException
     * @throws MissingCredentialsException
     * @throws NotFoundException
     */
    public function testReturnsTheDescriptionOfTheStartedImport(): void
    {
        $httpClient = new MockClient();
        $httpClient->addResponse($this->documentedResponse());

        $import = $this->createClient($httpClient)
            ->importTable($this->documentedRequest())
            ->importTableDescription
        ;

        self::assertInstanceOf(ImportTableDescription::class, $import);
        self::assertSame('3c9e1f0a-7b2d-4e8f-a6c5-1d0b9e8f7a6c', $import->clientToken);
        self::assertSame(
            'arn:aws:dynamodb:eu-central-1:123456789012:table/Music/import/01576624066799-e5f6a7b8',
            $import->importArn,
        );
        self::assertSame(ImportStatus::IN_PROGRESS, $import->importStatus);
        self::assertSame(InputCompressionType::GZIP, $import->inputCompressionType);
        self::assertSame(InputFormat::CSV, $import->inputFormat);
        self::assertSame(';', $import->inputFormatOptions?->csv?->delimiter);
        self::assertSame('music-imports', $import->s3BucketSource?->s3Bucket);
        self::assertSame('arn:aws:dynamodb:eu-central-1:123456789012:table/Music', $import->tableArn);
        self::assertSame('Music', $import->tableCreationParameters?->tableName);
        self::assertSame('e0a1b2c3-d4e5-4f60-8a7b-9c8d7e6f5a4b', $import->tableId);

        $startTime = $import->startTime;

        self::assertInstanceOf(DateTimeImmutable::class, $startTime);
        self::assertSame('2019-12-17T23:07:50.250000+00:00', $startTime->format('Y-m-d\TH:i:s.uP'));
    }

    /**
     * An import in progress has no end time and no counts yet.
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

        $import = $this->createClient($httpClient)
            ->importTable($this->documentedRequest())
            ->importTableDescription
        ;

        self::assertInstanceOf(ImportTableDescription::class, $import);
        self::assertNull($import->endTime);
        self::assertNull($import->errorCount);
        self::assertNull($import->failureCode);
        self::assertNull($import->failureMessage);
        self::assertNull($import->importedItemCount);
        self::assertNull($import->processedItemCount);
        self::assertNull($import->processedSizeBytes);
    }

    /**
     * @throws ExceptionInterface
     * @throws InvalidArgumentException
     * @throws MissingCredentialsException
     * @throws NotFoundException
     */
    public function testLeavesTheImportTableDescriptionNullWhenTheServiceOmitsIt(): void
    {
        $httpClient = new MockClient();
        $httpClient->addResponse(new Response(body: '{}'));

        $response = $this->createClient($httpClient)->importTable($this->documentedRequest());

        self::assertNull($response->importTableDescription);
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
        $httpClient->addResponse(new Response(body: '{"ImportTableDescription":{"InputFormat":"XML"}}'));

        $this->expectException(ResponseDeserializationException::class);

        $this->createClient($httpClient)->importTable($this->documentedRequest());
    }

    /**
     * @throws InvalidArgumentException
     */
    public function testRejectsAClientTokenWithADollarSign(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new ImportTableRequest(
            inputFormat: InputFormat::ION,
            s3BucketSource: new S3BucketSource('music-imports'),
            tableCreationParameters: new TableCreationParameters(
                attributeDefinitions: new AttributeDefinitionList([
                    new AttributeDefinition('Artist', ScalarAttributeType::STRING),
                ]),
                keySchema: new KeySchemaElementList([new KeySchemaElement('Artist', KeyType::HASH)]),
                tableName: 'Music',
            ),
            clientToken: 'music$import',
        );
    }

    /**
     * @throws InvalidArgumentException
     */
    private function documentedRequest(): ImportTableRequest
    {
        return new ImportTableRequest(
            inputFormat: InputFormat::CSV,
            s3BucketSource: new S3BucketSource(
                s3Bucket: 'music-imports',
                s3BucketOwner: '123456789012',
                s3KeyPrefix: 'imports/music',
            ),
            tableCreationParameters: new TableCreationParameters(
                attributeDefinitions: new AttributeDefinitionList([
                    new AttributeDefinition('Artist', ScalarAttributeType::STRING),
                    new AttributeDefinition('SongTitle', ScalarAttributeType::STRING),
                    new AttributeDefinition('AlbumTitle', ScalarAttributeType::STRING),
                ]),
                keySchema: new KeySchemaElementList([
                    new KeySchemaElement('Artist', KeyType::HASH),
                    new KeySchemaElement('SongTitle', KeyType::RANGE),
                ]),
                tableName: 'Music',
                billingMode: BillingMode::PAY_PER_REQUEST,
                globalSecondaryIndexes: new GlobalSecondaryIndexList([
                    new GlobalSecondaryIndex(
                        indexName: 'AlbumTitleIndex',
                        keySchema: new KeySchemaElementList([new KeySchemaElement('AlbumTitle', KeyType::HASH)]),
                        projection: new Projection(projectionType: ProjectionType::ALL),
                    ),
                ]),
                sseSpecification: new SSESpecification(enabled: true, sseType: SSEType::KMS),
            ),
            clientToken: '3c9e1f0a-7b2d-4e8f-a6c5-1d0b9e8f7a6c',
            inputCompressionType: InputCompressionType::GZIP,
            inputFormatOptions: InputFormatOptions::csv(';', ['Artist', 'SongTitle', 'AlbumTitle']),
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

    private function documentedResponse(): Response
    {
        $body = file_get_contents(self::RESPONSE_FIXTURE);

        self::assertIsString($body);

        return new Response(body: $body);
    }
}
