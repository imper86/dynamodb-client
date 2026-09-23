<?php

declare(strict_types=1);

namespace Imper86\DynamoDBClientTests\Message;

use Imper86\DynamoDBClient\Message\ImportTableRequest;
use Imper86\DynamoDBClient\Model\AttributeDefinition;
use Imper86\DynamoDBClient\Model\AttributeDefinitionList;
use Imper86\DynamoDBClient\Model\InputFormat;
use Imper86\DynamoDBClient\Model\KeySchemaElement;
use Imper86\DynamoDBClient\Model\KeySchemaElementList;
use Imper86\DynamoDBClient\Model\KeyType;
use Imper86\DynamoDBClient\Model\ScalarAttributeType;
use Imper86\DynamoDBClient\Model\TableCreationParameters;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(ImportTableRequest::class)]
final class ImportTableRequestTest extends TestCase
{
    /**
     * @throws InvalidArgumentException
     */
    public function testBuildsACsvImport(): void
    {
        $request = ImportTableRequest::csv(
            s3Bucket: 'music-imports',
            tableCreationParameters: $this->tableCreationParameters(),
            delimiter: ';',
            s3KeyPrefix: 'imports/music',
        );

        self::assertSame(InputFormat::CSV, $request->inputFormat);
        self::assertSame(';', $request->inputFormatOptions?->csv?->delimiter);
        self::assertNull($request->inputFormatOptions->csv->headerList);
        self::assertSame('music-imports', $request->s3BucketSource->s3Bucket);
        self::assertSame('imports/music', $request->s3BucketSource->s3KeyPrefix);
    }

    /**
     * @throws InvalidArgumentException
     */
    public function testLeavesOutTheCsvOptionsWhenNoneIsGiven(): void
    {
        $request = ImportTableRequest::csv('music-imports', $this->tableCreationParameters());

        self::assertSame(InputFormat::CSV, $request->inputFormat);
        self::assertNull($request->inputFormatOptions);
    }

    /**
     * @throws InvalidArgumentException
     */
    public function testBuildsADynamoDbJsonImport(): void
    {
        $request = ImportTableRequest::dynamoDbJson('music-imports', $this->tableCreationParameters());

        self::assertSame(InputFormat::DYNAMODB_JSON, $request->inputFormat);
        self::assertNull($request->inputFormatOptions);
    }

    /**
     * @throws InvalidArgumentException
     */
    public function testBuildsAnIonImport(): void
    {
        $request = ImportTableRequest::ion('music-imports', $this->tableCreationParameters(), s3BucketOwner: '123456789012');

        self::assertSame(InputFormat::ION, $request->inputFormat);
        self::assertSame('123456789012', $request->s3BucketSource->s3BucketOwner);
    }

    /**
     * @throws InvalidArgumentException
     */
    public function testValidatesThroughTheConstructors(): void
    {
        $this->expectException(InvalidArgumentException::class);

        ImportTableRequest::ion('music imports', $this->tableCreationParameters());
    }

    /**
     * @throws InvalidArgumentException
     */
    private function tableCreationParameters(): TableCreationParameters
    {
        return new TableCreationParameters(
            attributeDefinitions: new AttributeDefinitionList([
                new AttributeDefinition('Artist', ScalarAttributeType::STRING),
            ]),
            keySchema: new KeySchemaElementList([new KeySchemaElement('Artist', KeyType::HASH)]),
            tableName: 'Music',
        );
    }
}
