<?php

declare(strict_types=1);

namespace Imper86\DynamoDBClient\Message;

use DateTimeImmutable;
use InvalidArgumentException;
use Imper86\DynamoDBClient\Model\ExportFormat;
use Imper86\DynamoDBClient\Model\ExportType;
use Imper86\DynamoDBClient\Model\ExportViewType;
use Imper86\DynamoDBClient\Model\IncrementalExportSpecification;
use Imper86\DynamoDBClient\Model\S3SseAlgorithm;
use Webmozart\Assert\Assert;

final class ExportTableToPointInTimeRequest
{
    /**
     * An `INCREMENTAL_EXPORT` needs the `IncrementalExportSpecification` that says which period to export;
     * {@see self::full()} and {@see self::incremental()} build either kind.
     *
     * @param non-empty-string $s3Bucket the bucket to export the snapshot to
     * @param non-empty-string $tableArn the ARN of the table to export
     * @param null|non-empty-string $clientToken makes the call idempotent for eight hours after it completes
     * @param null|DateTimeImmutable $exportTime the point in the past to export the table as of
     * @param null|non-empty-string $s3BucketOwner the twelve-digit account ID owning the bucket, required when
     *                                             it is not the caller's
     * @param null|string $s3Prefix the key prefix to store the export under
     * @param null|non-empty-string $s3SseKmsKeyId the KMS key encrypting the bucket, for an algorithm of KMS
     * @throws InvalidArgumentException
     */
    public function __construct(
        public readonly string $s3Bucket,
        public readonly string $tableArn,
        public readonly ?string $clientToken = null,
        public readonly ?ExportFormat $exportFormat = null,
        public readonly ?DateTimeImmutable $exportTime = null,
        public readonly ?ExportType $exportType = null,
        public readonly ?IncrementalExportSpecification $incrementalExportSpecification = null,
        public readonly ?string $s3BucketOwner = null,
        public readonly ?string $s3Prefix = null,
        public readonly ?S3SseAlgorithm $s3SseAlgorithm = null,
        public readonly ?string $s3SseKmsKeyId = null,
    ) {
        Assert::stringNotEmpty($this->s3Bucket);
        Assert::maxLength($this->s3Bucket, 255);
        Assert::regex($this->s3Bucket, '/^[a-z0-9A-Z]+[.\-\w]*[a-z0-9A-Z]+$/');
        Assert::stringNotEmpty($this->tableArn);
        Assert::maxLength($this->tableArn, 1024);
        Assert::nullOrRegex($this->clientToken, '/^[^$]+$/');
        Assert::nullOrRegex($this->s3BucketOwner, '/^[0-9]{12}$/');
        Assert::nullOrMaxLength($this->s3Prefix, 1024);
        Assert::nullOrStringNotEmpty($this->s3SseKmsKeyId);
        Assert::nullOrMaxLength($this->s3SseKmsKeyId, 2048);

        if (ExportType::INCREMENTAL_EXPORT === $this->exportType) {
            Assert::notNull(
                $this->incrementalExportSpecification,
                'An incremental export needs an IncrementalExportSpecification.',
            );
        }
    }

    /**
     * Exports a snapshot of the whole table, as it is now or as it was at `exportTime`.
     *
     * @param non-empty-string $s3Bucket the bucket to export the snapshot to
     * @param non-empty-string $tableArn the ARN of the table to export
     * @param null|non-empty-string $clientToken makes the call idempotent for eight hours after it completes
     * @param null|DateTimeImmutable $exportTime the point in the past to export the table as of
     * @param null|non-empty-string $s3BucketOwner the twelve-digit account ID owning the bucket, required when
     *                                             it is not the caller's
     * @param null|string $s3Prefix the key prefix to store the export under
     * @param null|non-empty-string $s3SseKmsKeyId the KMS key encrypting the bucket, for an algorithm of KMS
     * @throws InvalidArgumentException
     */
    public static function full(
        string $s3Bucket,
        string $tableArn,
        ?string $clientToken = null,
        ?ExportFormat $exportFormat = null,
        ?DateTimeImmutable $exportTime = null,
        ?string $s3BucketOwner = null,
        ?string $s3Prefix = null,
        ?S3SseAlgorithm $s3SseAlgorithm = null,
        ?string $s3SseKmsKeyId = null,
    ): self {
        return new self(
            s3Bucket: $s3Bucket,
            tableArn: $tableArn,
            clientToken: $clientToken,
            exportFormat: $exportFormat,
            exportTime: $exportTime,
            exportType: ExportType::FULL_EXPORT,
            s3BucketOwner: $s3BucketOwner,
            s3Prefix: $s3Prefix,
            s3SseAlgorithm: $s3SseAlgorithm,
            s3SseKmsKeyId: $s3SseKmsKeyId,
        );
    }

    /**
     * Exports the items that changed within a period.
     *
     * @param non-empty-string $s3Bucket the bucket to export the snapshot to
     * @param non-empty-string $tableArn the ARN of the table to export
     * @param null|non-empty-string $clientToken makes the call idempotent for eight hours after it completes
     * @param null|DateTimeImmutable $exportFromTime the start of the exported period, inclusive
     * @param null|DateTimeImmutable $exportToTime the end of the exported period, exclusive; the latest time
     *                                             with data available when absent
     * @param null|ExportViewType $exportViewType which images of a changed item the export holds
     * @param null|non-empty-string $s3BucketOwner the twelve-digit account ID owning the bucket, required when
     *                                             it is not the caller's
     * @param null|string $s3Prefix the key prefix to store the export under
     * @param null|non-empty-string $s3SseKmsKeyId the KMS key encrypting the bucket, for an algorithm of KMS
     * @throws InvalidArgumentException
     */
    public static function incremental(
        string $s3Bucket,
        string $tableArn,
        ?string $clientToken = null,
        ?ExportFormat $exportFormat = null,
        ?DateTimeImmutable $exportFromTime = null,
        ?DateTimeImmutable $exportToTime = null,
        ?ExportViewType $exportViewType = null,
        ?string $s3BucketOwner = null,
        ?string $s3Prefix = null,
        ?S3SseAlgorithm $s3SseAlgorithm = null,
        ?string $s3SseKmsKeyId = null,
    ): self {
        return new self(
            s3Bucket: $s3Bucket,
            tableArn: $tableArn,
            clientToken: $clientToken,
            exportFormat: $exportFormat,
            exportType: ExportType::INCREMENTAL_EXPORT,
            incrementalExportSpecification: new IncrementalExportSpecification(
                exportFromTime: $exportFromTime,
                exportToTime: $exportToTime,
                exportViewType: $exportViewType,
            ),
            s3BucketOwner: $s3BucketOwner,
            s3Prefix: $s3Prefix,
            s3SseAlgorithm: $s3SseAlgorithm,
            s3SseKmsKeyId: $s3SseKmsKeyId,
        );
    }
}
