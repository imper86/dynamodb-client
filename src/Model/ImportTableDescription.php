<?php

declare(strict_types=1);

namespace Imper86\DynamoDBClient\Model;

use DateTimeImmutable;

final readonly class ImportTableDescription
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
        public ?string $clientToken = null,
        public ?string $cloudWatchLogGroupArn = null,
        public ?DateTimeImmutable $endTime = null,
        public ?int $errorCount = null,
        public ?string $failureCode = null,
        public ?string $failureMessage = null,
        public ?string $importArn = null,
        public ?int $importedItemCount = null,
        public ?ImportStatus $importStatus = null,
        public ?InputCompressionType $inputCompressionType = null,
        public ?InputFormat $inputFormat = null,
        public ?InputFormatOptions $inputFormatOptions = null,
        public ?int $processedItemCount = null,
        public ?int $processedSizeBytes = null,
        public ?S3BucketSource $s3BucketSource = null,
        public ?DateTimeImmutable $startTime = null,
        public ?string $tableArn = null,
        public ?TableCreationParameters $tableCreationParameters = null,
        public ?string $tableId = null,
    ) {}
}
