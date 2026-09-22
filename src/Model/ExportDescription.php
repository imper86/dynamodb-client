<?php

declare(strict_types=1);

namespace Imper86\DynamoDBClient\Model;

use DateTimeImmutable;

final readonly class ExportDescription
{
    /**
     * @param null|DateTimeImmutable $endTime the moment the export finished; absent while it is in progress
     * @param null|string $exportManifest the name of the export's manifest file
     * @param null|DateTimeImmutable $exportTime the point in time the table was exported as of
     * @param null|string $failureCode why the export failed; only a failed export carries it
     * @param null|IncrementalExportSpecification $incrementalExportSpecification the exported period; only an
     *                                                                            incremental export carries it
     */
    public function __construct(
        public ?int $billedSizeBytes = null,
        public ?string $clientToken = null,
        public ?DateTimeImmutable $endTime = null,
        public ?string $exportArn = null,
        public ?ExportFormat $exportFormat = null,
        public ?string $exportManifest = null,
        public ?ExportStatus $exportStatus = null,
        public ?DateTimeImmutable $exportTime = null,
        public ?ExportType $exportType = null,
        public ?string $failureCode = null,
        public ?string $failureMessage = null,
        public ?IncrementalExportSpecification $incrementalExportSpecification = null,
        public ?int $itemCount = null,
        public ?string $s3Bucket = null,
        public ?string $s3BucketOwner = null,
        public ?string $s3Prefix = null,
        public ?S3SseAlgorithm $s3SseAlgorithm = null,
        public ?string $s3SseKmsKeyId = null,
        public ?DateTimeImmutable $startTime = null,
        public ?string $tableArn = null,
        public ?string $tableId = null,
    ) {}
}
