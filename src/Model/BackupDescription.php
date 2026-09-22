<?php

declare(strict_types=1);

namespace Imper86\DynamoDBClient\Model;

/**
 * The description of a backup: what the backup itself is, and what the table looked like when it
 * was taken.
 */
final readonly class BackupDescription
{
    public function __construct(
        public ?BackupDetails $backupDetails = null,
        public ?SourceTableDetails $sourceTableDetails = null,
        public ?SourceTableFeatureDetails $sourceTableFeatureDetails = null,
    ) {}
}
