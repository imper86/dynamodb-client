<?php

declare(strict_types=1);

namespace Imper86\DynamoDBClient\Model;

use DateTimeImmutable;

final class ImportTableDescription
{
    /**
     * @param null|DateTimeImmutable $endTime the moment the import finished; absent while it is in progress
     * @param null|int $errorCount how many items could not be imported
     * @param null|string $failureCode why the import failed; only a failed import carries it
     * @param null|int $importedItemCount how many items were written to the table
     * @param null|int $processedItemCount how many items were read from the source, imported or not
     * @param null|TableCreationParameters $tableCreationParameters the table the import creates, as requested
     */
    public function __construct(
        public readonly ?string $clientToken = null,
        public readonly ?string $cloudWatchLogGroupArn = null,
        public readonly ?DateTimeImmutable $endTime = null,
        public readonly ?int $errorCount = null,
        public readonly ?string $failureCode = null,
        public readonly ?string $failureMessage = null,
        public readonly ?string $importArn = null,
        public readonly ?int $importedItemCount = null,
        public readonly ?ImportStatus $importStatus = null,
        public readonly ?InputCompressionType $inputCompressionType = null,
        public readonly ?InputFormat $inputFormat = null,
        public readonly ?InputFormatOptions $inputFormatOptions = null,
        public readonly ?int $processedItemCount = null,
        public readonly ?int $processedSizeBytes = null,
        public readonly ?S3BucketSource $s3BucketSource = null,
        public readonly ?DateTimeImmutable $startTime = null,
        public readonly ?string $tableArn = null,
        public readonly ?TableCreationParameters $tableCreationParameters = null,
        public readonly ?string $tableId = null,
    ) {}
}
