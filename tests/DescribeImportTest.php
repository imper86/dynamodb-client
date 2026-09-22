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
use Imper86\DynamoDBClient\Message\DescribeImportRequest;
use Imper86\DynamoDBClient\Model\AttributeDefinition;
use Imper86\DynamoDBClient\Model\AttributeDefinitionList;
use Imper86\DynamoDBClient\Model\BillingMode;
use Imper86\DynamoDBClient\Model\Credentials;
use Imper86\DynamoDBClient\Model\CsvOptions;
use Imper86\DynamoDBClient\Model\GlobalSecondaryIndex;
use Imper86\DynamoDBClient\Model\ImportStatus;
use Imper86\DynamoDBClient\Model\ImportTableDescription;
use Imper86\DynamoDBClient\Model\InputCompressionType;
use Imper86\DynamoDBClient\Model\InputFormat;
use Imper86\DynamoDBClient\Model\KeySchemaElement;
use Imper86\DynamoDBClient\Model\KeySchemaElementList;
use Imper86\DynamoDBClient\Model\KeyType;
use Imper86\DynamoDBClient\Model\ProjectionType;
use Imper86\DynamoDBClient\Model\S3BucketSource;
use Imper86\DynamoDBClient\Model\ScalarAttributeType;
use Imper86\DynamoDBClient\Model\SSESpecification;
use Imper86\DynamoDBClient\Model\SSEType;
use Imper86\DynamoDBClient\Model\TableCreationParameters;
use Imper86\DynamoDBClient\ValueObject\NonEmptyStringList;
use InvalidArgumentException;
use Nyholm\Psr7\Request;
use Nyholm\Psr7\Response;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

use function array_fill;
use function array_map;
use function file_get_contents;
use function str_repeat;

/**
 * The DescribeImport reference has no Examples section, so the fixtures are built from its request and
 * response syntax, describing a completed import of gzipped, semicolon-separated CSV files into a new
 * on-demand `Music` table with one global secondary index. The failed and in-progress variants are
 * inlined in the tests that need them.
 *
 * `TableCreationParameters`, `S3BucketSource` and `CsvOptions` are the parameters of ImportTable echoed
 * back, so they validate like the request models they are; their rules are tested here.
 *
 * @see https://docs.aws.amazon.com/amazondynamodb/latest/APIReference/API_DescribeImport.html
 * @internal
 */
#[CoversClass(DynamoDBClient::class)]
final class DescribeImportTest extends TestCase
{
    private const string IMPORT_ARN =
        'arn:aws:dynamodb:eu-central-1:123456789012:table/Music/import/01576624066799-e5f6a7b8';

    private const string REQUEST_FIXTURE = __DIR__ . '/fixtures/describe-import-request.json';

    private const string RESPONSE_FIXTURE = __DIR__ . '/fixtures/describe-import-response.json';

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

        $this->createClient($httpClient)->describeImport(new DescribeImportRequest(self::IMPORT_ARN));

        $sent = $httpClient->getLastRequest();

        self::assertInstanceOf(Request::class, $sent);
        self::assertSame('DynamoDB_20120810.DescribeImport', $sent->getHeaderLine('X-Amz-Target'));
        self::assertJsonStringEqualsJsonFile(self::REQUEST_FIXTURE, $sent->getBody()->__toString());
    }

    /**
     * @throws ExceptionInterface
     * @throws InvalidArgumentException
     * @throws MissingCredentialsException
     * @throws NotFoundException
     */
    public function testReturnsTheProgressOfTheImport(): void
    {
        $import = $this->describeDocumentedImport();

        self::assertSame('3c9e1f0a-7b2d-4e8f-a6c5-1d0b9e8f7a6c', $import->clientToken);
        self::assertSame(
            'arn:aws:logs:eu-central-1:123456789012:log-group:/aws-dynamodb/imports:*',
            $import->cloudWatchLogGroupArn,
        );
        self::assertSame(2, $import->errorCount);
        self::assertNull($import->failureCode);
        self::assertNull($import->failureMessage);
        self::assertSame(self::IMPORT_ARN, $import->importArn);
        self::assertSame(40, $import->importedItemCount);
        self::assertSame(ImportStatus::COMPLETED, $import->importStatus);
        self::assertSame(42, $import->processedItemCount);
        self::assertSame(123456, $import->processedSizeBytes);
        self::assertSame('arn:aws:dynamodb:eu-central-1:123456789012:table/Music', $import->tableArn);
        self::assertSame('e0a1b2c3-d4e5-4f60-8a7b-9c8d7e6f5a4b', $import->tableId);

        $startTime = $import->startTime;
        $endTime = $import->endTime;

        self::assertInstanceOf(DateTimeImmutable::class, $startTime);
        self::assertInstanceOf(DateTimeImmutable::class, $endTime);
        self::assertSame('2019-12-17T23:07:50.250000+00:00', $startTime->format('Y-m-d\TH:i:s.uP'));
        self::assertSame('2019-12-17T23:12:46.500000+00:00', $endTime->format('Y-m-d\TH:i:s.uP'));
    }

    /**
     * @throws ExceptionInterface
     * @throws InvalidArgumentException
     * @throws MissingCredentialsException
     * @throws NotFoundException
     */
    public function testReturnsTheSourceOfTheImport(): void
    {
        $import = $this->describeDocumentedImport();

        self::assertSame(InputCompressionType::GZIP, $import->inputCompressionType);
        self::assertSame(InputFormat::CSV, $import->inputFormat);

        $source = $import->s3BucketSource;

        self::assertInstanceOf(S3BucketSource::class, $source);
        self::assertSame('music-imports', $source->s3Bucket);
        self::assertSame('123456789012', $source->s3BucketOwner);
        self::assertSame('imports/music', $source->s3KeyPrefix);

        $csv = $import->inputFormatOptions?->csv;

        self::assertInstanceOf(CsvOptions::class, $csv);
        self::assertSame(';', $csv->delimiter);
        self::assertSame(['Artist', 'SongTitle', 'AlbumTitle'], $csv->headerList?->toArray());
    }

    /**
     * @throws ExceptionInterface
     * @throws InvalidArgumentException
     * @throws MissingCredentialsException
     * @throws NotFoundException
     */
    public function testReturnsTheTableTheImportCreates(): void
    {
        $table = $this->describeDocumentedImport()->tableCreationParameters;

        self::assertInstanceOf(TableCreationParameters::class, $table);
        self::assertSame('Music', $table->tableName);
        self::assertSame(BillingMode::PAY_PER_REQUEST, $table->billingMode);
        $attributes = $table->attributeDefinitions;

        self::assertCount(3, $attributes);
        self::assertSame(['Artist', 'SongTitle', 'AlbumTitle'], array_map(
            static fn(AttributeDefinition $attribute): string => $attribute->attributeName,
            $attributes->toArray(),
        ));
        self::assertSame(ScalarAttributeType::STRING, $attributes->get(0)?->attributeType);

        $partitionKey = $table->keySchema->get(0);
        $sortKey = $table->keySchema->get(1);

        self::assertInstanceOf(KeySchemaElement::class, $partitionKey);
        self::assertInstanceOf(KeySchemaElement::class, $sortKey);
        self::assertSame('Artist', $partitionKey->attributeName);
        self::assertSame(KeyType::HASH, $partitionKey->keyType);
        self::assertSame('SongTitle', $sortKey->attributeName);
        self::assertSame(KeyType::RANGE, $sortKey->keyType);
        self::assertNull($table->onDemandThroughput);
        self::assertNull($table->provisionedThroughput);
        self::assertNull($table->vectorIndexes);

        $index = $table->globalSecondaryIndexes?->get(0);

        self::assertInstanceOf(GlobalSecondaryIndex::class, $index);
        self::assertSame('AlbumTitleIndex', $index->indexName);
        self::assertSame(ProjectionType::ALL, $index->projection->projectionType);

        $encryption = $table->sseSpecification;

        self::assertInstanceOf(SSESpecification::class, $encryption);
        self::assertTrue($encryption->enabled);
        self::assertSame(SSEType::KMS, $encryption->sseType);
    }

    /**
     * A failed import reports why, and has no end time.
     *
     * @throws ExceptionInterface
     * @throws InvalidArgumentException
     * @throws MissingCredentialsException
     * @throws NotFoundException
     */
    public function testReturnsWhyAnImportFailed(): void
    {
        $httpClient = new MockClient();
        $httpClient->addResponse(new Response(body: '{"ImportTableDescription":{"ImportStatus":"FAILED",'
            . '"FailureCode":"S3NoSuchBucket","FailureMessage":"The specified bucket does not exist"}}'));

        $import = $this->createClient($httpClient)
            ->describeImport(new DescribeImportRequest(self::IMPORT_ARN))
            ->importTableDescription
        ;

        self::assertInstanceOf(ImportTableDescription::class, $import);
        self::assertSame(ImportStatus::FAILED, $import->importStatus);
        self::assertSame('S3NoSuchBucket', $import->failureCode);
        self::assertSame('The specified bucket does not exist', $import->failureMessage);
        self::assertNull($import->endTime);
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
        $httpClient->addResponse(new Response(body: '{"ImportTableDescription":{"ImportStatus":"IN_PROGRESS",'
            . '"InputFormat":"ION","S3BucketSource":{"S3Bucket":"music-imports"}}}'));

        $import = $this->createClient($httpClient)
            ->describeImport(new DescribeImportRequest(self::IMPORT_ARN))
            ->importTableDescription
        ;

        self::assertInstanceOf(ImportTableDescription::class, $import);
        self::assertSame(ImportStatus::IN_PROGRESS, $import->importStatus);
        self::assertNull($import->inputCompressionType);
        self::assertNull($import->inputFormatOptions);
        self::assertNull($import->tableCreationParameters);

        $source = $import->s3BucketSource;

        self::assertInstanceOf(S3BucketSource::class, $source);
        self::assertNull($source->s3BucketOwner);
        self::assertNull($source->s3KeyPrefix);
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

        $response = $this->createClient($httpClient)->describeImport(new DescribeImportRequest(self::IMPORT_ARN));

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
        $httpClient->addResponse(new Response(body: '{"ImportTableDescription":{"ImportStatus":"PAUSED"}}'));

        $this->expectException(ResponseDeserializationException::class);

        $this->createClient($httpClient)->describeImport(new DescribeImportRequest(self::IMPORT_ARN));
    }

    /**
     * @throws InvalidArgumentException
     */
    public function testRejectsAnImportArnShorterThanThirtySevenCharacters(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new DescribeImportRequest(str_repeat('a', 36));
    }

    /**
     * @throws InvalidArgumentException
     */
    public function testRejectsAnImportArnLongerThanTheServiceAllows(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new DescribeImportRequest(str_repeat('a', 1025));
    }

    /**
     * @throws InvalidArgumentException
     */
    public function testRejectsADelimiterTheServiceDoesNotSupport(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new CsvOptions(delimiter: '#');
    }

    /**
     * @throws InvalidArgumentException
     */
    public function testRejectsADelimiterOfMoreThanOneCharacter(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new CsvOptions(delimiter: ';;');
    }

    /**
     * @throws InvalidArgumentException
     */
    public function testRejectsAnEmptyHeaderList(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new CsvOptions(headerList: new NonEmptyStringList([]));
    }

    /**
     * @throws InvalidArgumentException
     */
    public function testRejectsMoreHeadersThanTheServiceAccepts(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new CsvOptions(headerList: new NonEmptyStringList(array_fill(0, 256, 'Column')));
    }

    /**
     * @throws InvalidArgumentException
     */
    public function testRejectsAHeaderLongerThanTheServiceAllows(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new CsvOptions(headerList: new NonEmptyStringList([str_repeat('a', 65537)]));
    }

    /**
     * A header cannot hold a comma or a double quote.
     *
     * @throws InvalidArgumentException
     */
    public function testRejectsAHeaderWithACharacterThePatternDoesNotAllow(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new CsvOptions(headerList: new NonEmptyStringList(['Artist,SongTitle']));
    }

    /**
     * @throws InvalidArgumentException
     */
    public function testRejectsABucketNameLongerThanTheServiceAllows(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new S3BucketSource(str_repeat('a', 256));
    }

    /**
     * @throws InvalidArgumentException
     */
    public function testRejectsABucketNameWithACharacterThePatternDoesNotAllow(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new S3BucketSource('music-imports-');
    }

    /**
     * @throws InvalidArgumentException
     */
    public function testRejectsABucketOwnerThatIsNotAnAccountId(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new S3BucketSource('music-imports', s3BucketOwner: '12345678901');
    }

    /**
     * @throws InvalidArgumentException
     */
    public function testRejectsAKeyPrefixLongerThanTheServiceAllows(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new S3BucketSource('music-imports', s3KeyPrefix: str_repeat('a', 1025));
    }

    /**
     * @throws InvalidArgumentException
     */
    public function testRejectsATableNameShorterThanThreeCharacters(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->createTableCreationParameters('Mu');
    }

    /**
     * @throws InvalidArgumentException
     */
    public function testRejectsATableNameLongerThanTheServiceAllows(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->createTableCreationParameters(str_repeat('a', 256));
    }

    /**
     * The table is created by name; an ARN is not accepted.
     *
     * @throws InvalidArgumentException
     */
    public function testRejectsATableNameWithACharacterThePatternDoesNotAllow(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->createTableCreationParameters('arn:aws:dynamodb:eu-central-1:123456789012:table/Music');
    }

    /**
     * @throws InvalidArgumentException
     */
    public function testRejectsAnEmptyKeySchema(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new TableCreationParameters(
            attributeDefinitions: new AttributeDefinitionList([]),
            keySchema: new KeySchemaElementList([]),
            tableName: 'Music',
        );
    }

    /**
     * @param non-empty-string $tableName
     * @throws InvalidArgumentException
     */
    private function createTableCreationParameters(string $tableName): TableCreationParameters
    {
        return new TableCreationParameters(
            attributeDefinitions: new AttributeDefinitionList([
                new AttributeDefinition('Artist', ScalarAttributeType::STRING),
            ]),
            keySchema: new KeySchemaElementList([new KeySchemaElement('Artist', KeyType::HASH)]),
            tableName: $tableName,
        );
    }

    /**
     * @throws ExceptionInterface
     * @throws InvalidArgumentException
     * @throws MissingCredentialsException
     * @throws NotFoundException
     */
    private function describeDocumentedImport(): ImportTableDescription
    {
        $httpClient = new MockClient();
        $httpClient->addResponse($this->documentedResponse());

        $import = $this->createClient($httpClient)
            ->describeImport(new DescribeImportRequest(self::IMPORT_ARN))
            ->importTableDescription
        ;

        self::assertInstanceOf(ImportTableDescription::class, $import);

        return $import;
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
