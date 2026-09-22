<?php

declare(strict_types=1);

namespace Imper86\DynamoDBClient\Message;

use DateTimeImmutable;
use InvalidArgumentException;
use Imper86\DynamoDBClient\Model\ExportFormat;
use Imper86\DynamoDBClient\Model\ExportType;
use Imper86\DynamoDBClient\Model\IncrementalExportSpecification;
use Imper86\DynamoDBClient\Model\S3SseAlgorithm;
use Webmozart\Assert\Assert;

final readonly class ExportTableToPointInTimeRequest
{
    /**
     * An `INCREMENTAL_EXPORT` needs the `IncrementalExportSpecification` that says which period to export.
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
        public string $s3Bucket,
        public string $tableArn,
        public ?string $clientToken = null,
        public ?ExportFormat $exportFormat = null,
        public ?DateTimeImmutable $exportTime = null,
        public ?ExportType $exportType = null,
        public ?IncrementalExportSpecification $incrementalExportSpecification = null,
        public ?string $s3BucketOwner = null,
        public ?string $s3Prefix = null,
        public ?S3SseAlgorithm $s3SseAlgorithm = null,
        public ?string $s3SseKmsKeyId = null,
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
}
