<?php

declare(strict_types=1);

namespace Imper86\DynamoDBClient\Model;

use DateTimeImmutable;

final class ImportSummary
{
    /**
     * @param null|DateTimeImmutable $endTime the moment the import finished; absent while it is in progress
     */
    public function __construct(
        public readonly ?string $cloudWatchLogGroupArn = null,
        public readonly ?DateTimeImmutable $endTime = null,
        public readonly ?string $importArn = null,
        public readonly ?ImportStatus $importStatus = null,
        public readonly ?InputFormat $inputFormat = null,
        public readonly ?S3BucketSource $s3BucketSource = null,
        public readonly ?DateTimeImmutable $startTime = null,
        public readonly ?string $tableArn = null,
    ) {}
}
