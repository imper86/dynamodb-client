<?php

declare(strict_types=1);

namespace Imper86\DynamoDBClient\Model;

use DateTimeImmutable;

final class BackupDetails
{
    /**
     * @param null|int $backupSizeBytes the size of the backup, which DynamoDB refreshes about every six hours
     * @param null|DateTimeImmutable $backupExpiryDateTime the moment a SYSTEM backup expires; a USER backup has none
     */
    public function __construct(
        public readonly ?string $backupArn = null,
        public readonly ?DateTimeImmutable $backupCreationDateTime = null,
        public readonly ?DateTimeImmutable $backupExpiryDateTime = null,
        public readonly ?string $backupName = null,
        public readonly ?int $backupSizeBytes = null,
        public readonly ?BackupStatus $backupStatus = null,
        public readonly ?BackupType $backupType = null,
    ) {}
}
