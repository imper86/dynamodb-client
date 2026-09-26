<?php

declare(strict_types=1);

namespace Imper86\DynamoDBClient\Model;

use DateTimeImmutable;

final class ExportDescription
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
        public readonly ?int $billedSizeBytes = null,
        public readonly ?string $clientToken = null,
        public readonly ?DateTimeImmutable $endTime = null,
        public readonly ?string $exportArn = null,
        public readonly ?ExportFormat $exportFormat = null,
        public readonly ?string $exportManifest = null,
        public readonly ?ExportStatus $exportStatus = null,
        public readonly ?DateTimeImmutable $exportTime = null,
        public readonly ?ExportType $exportType = null,
        public readonly ?string $failureCode = null,
        public readonly ?string $failureMessage = null,
        public readonly ?IncrementalExportSpecification $incrementalExportSpecification = null,
        public readonly ?int $itemCount = null,
        public readonly ?string $s3Bucket = null,
        public readonly ?string $s3BucketOwner = null,
        public readonly ?string $s3Prefix = null,
        public readonly ?S3SseAlgorithm $s3SseAlgorithm = null,
        public readonly ?string $s3SseKmsKeyId = null,
        public readonly ?DateTimeImmutable $startTime = null,
        public readonly ?string $tableArn = null,
        public readonly ?string $tableId = null,
    ) {}
}
