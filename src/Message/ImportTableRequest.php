<?php

declare(strict_types=1);

namespace Imper86\DynamoDBClient\Message;

use Imper86\DynamoDBClient\Model\InputCompressionType;
use Imper86\DynamoDBClient\Model\InputFormat;
use Imper86\DynamoDBClient\Model\InputFormatOptions;
use Imper86\DynamoDBClient\Model\S3BucketSource;
use Imper86\DynamoDBClient\Model\TableCreationParameters;
use InvalidArgumentException;
use Webmozart\Assert\Assert;

final class ImportTableRequest
{
    /**
     * {@see self::csv()}, {@see self::dynamoDbJson()} and {@see self::ion()} pick the format, and only the
     * CSV one takes layout options.
     *
     * @param TableCreationParameters $tableCreationParameters the table to create and import the data into
     * @param null|non-empty-string $clientToken makes the call idempotent for eight hours after it completes
     * @param null|InputFormatOptions $inputFormatOptions how a CSV source is laid out
     * @throws InvalidArgumentException
     */
    public function __construct(
        public readonly InputFormat $inputFormat,
        public readonly S3BucketSource $s3BucketSource,
        public readonly TableCreationParameters $tableCreationParameters,
        public readonly ?string $clientToken = null,
        public readonly ?InputCompressionType $inputCompressionType = null,
        public readonly ?InputFormatOptions $inputFormatOptions = null,
    ) {
        Assert::nullOrRegex($this->clientToken, '/^[^$]+$/');
    }

    /**
     * Imports CSV files. The layout options go on the wire only when one of them is given.
     *
     * @param non-empty-string $s3Bucket the bucket holding the files to import
     * @param TableCreationParameters $tableCreationParameters the table to create and import the data into
     * @param null|non-empty-string $clientToken makes the call idempotent for eight hours after it completes
     * @param null|non-empty-string $delimiter a single character: comma, semicolon, colon, pipe, tab or space;
     *                                         DynamoDB defaults to a comma
     * @param null|array<non-empty-string> $headerList the column names, when the files have no header line of
     *                                                 their own
     * @param null|non-empty-string $s3BucketOwner the twelve-digit account ID owning the bucket, when it is
     *                                             not the caller's
     * @param null|string $s3KeyPrefix the key prefix shared by every file to import
     * @throws InvalidArgumentException
     */
    public static function csv(
        string $s3Bucket,
        TableCreationParameters $tableCreationParameters,
        ?string $clientToken = null,
        ?string $delimiter = null,
        ?array $headerList = null,
        ?InputCompressionType $inputCompressionType = null,
        ?string $s3BucketOwner = null,
        ?string $s3KeyPrefix = null,
    ): self {
        return new self(
            inputFormat: InputFormat::CSV,
            s3BucketSource: new S3BucketSource($s3Bucket, $s3BucketOwner, $s3KeyPrefix),
            tableCreationParameters: $tableCreationParameters,
            clientToken: $clientToken,
            inputCompressionType: $inputCompressionType,
            inputFormatOptions: null === $delimiter && null === $headerList
                ? null
                : InputFormatOptions::csv($delimiter, $headerList),
        );
    }

    /**
     * Imports files in DynamoDB JSON format.
     *
     * @param non-empty-string $s3Bucket the bucket holding the files to import
     * @param TableCreationParameters $tableCreationParameters the table to create and import the data into
     * @param null|non-empty-string $clientToken makes the call idempotent for eight hours after it completes
     * @param null|non-empty-string $s3BucketOwner the twelve-digit account ID owning the bucket, when it is
     *                                             not the caller's
     * @param null|string $s3KeyPrefix the key prefix shared by every file to import
     * @throws InvalidArgumentException
     */
    public static function dynamoDbJson(
        string $s3Bucket,
        TableCreationParameters $tableCreationParameters,
        ?string $clientToken = null,
        ?InputCompressionType $inputCompressionType = null,
        ?string $s3BucketOwner = null,
        ?string $s3KeyPrefix = null,
    ): self {
        return new self(
            inputFormat: InputFormat::DYNAMODB_JSON,
            s3BucketSource: new S3BucketSource($s3Bucket, $s3BucketOwner, $s3KeyPrefix),
            tableCreationParameters: $tableCreationParameters,
            clientToken: $clientToken,
            inputCompressionType: $inputCompressionType,
        );
    }

    /**
     * Imports files in Amazon Ion format.
     *
     * @param non-empty-string $s3Bucket the bucket holding the files to import
     * @param TableCreationParameters $tableCreationParameters the table to create and import the data into
     * @param null|non-empty-string $clientToken makes the call idempotent for eight hours after it completes
     * @param null|non-empty-string $s3BucketOwner the twelve-digit account ID owning the bucket, when it is
     *                                             not the caller's
     * @param null|string $s3KeyPrefix the key prefix shared by every file to import
     * @throws InvalidArgumentException
     */
    public static function ion(
        string $s3Bucket,
        TableCreationParameters $tableCreationParameters,
        ?string $clientToken = null,
        ?InputCompressionType $inputCompressionType = null,
        ?string $s3BucketOwner = null,
        ?string $s3KeyPrefix = null,
    ): self {
        return new self(
            inputFormat: InputFormat::ION,
            s3BucketSource: new S3BucketSource($s3Bucket, $s3BucketOwner, $s3KeyPrefix),
            tableCreationParameters: $tableCreationParameters,
            clientToken: $clientToken,
            inputCompressionType: $inputCompressionType,
        );
    }
}
