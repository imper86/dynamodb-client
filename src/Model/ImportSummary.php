<?php

declare(strict_types=1);

namespace Imper86\DynamoDBClient\Model;

use DateTimeImmutable;

final readonly class ImportSummary
{
    /**
     * @param null|DateTimeImmutable $endTime the moment the import finished; absent while it is in progress
     */
    public function __construct(
        public ?string $cloudWatchLogGroupArn = null,
        public ?DateTimeImmutable $endTime = null,
        public ?string $importArn = null,
        public ?ImportStatus $importStatus = null,
        public ?InputFormat $inputFormat = null,
        public ?S3BucketSource $s3BucketSource = null,
        public ?DateTimeImmutable $startTime = null,
        public ?string $tableArn = null,
    ) {}
}
