<?php

declare(strict_types=1);

namespace Imper86\DynamoDBClient\Model;

use DateTimeImmutable;

final readonly class BackupSummary
{
    /**
     * @param null|DateTimeImmutable $backupExpiryDateTime the moment a SYSTEM backup expires; a USER backup has none
     * @param null|int $backupSizeBytes the size of the backup, which DynamoDB refreshes about every six hours
     */
    public function __construct(
        public ?string $backupArn = null,
        public ?DateTimeImmutable $backupCreationDateTime = null,
        public ?DateTimeImmutable $backupExpiryDateTime = null,
        public ?string $backupName = null,
        public ?int $backupSizeBytes = null,
        public ?BackupStatus $backupStatus = null,
        public ?BackupType $backupType = null,
        public ?string $tableArn = null,
        public ?string $tableId = null,
        public ?string $tableName = null,
    ) {}
}
