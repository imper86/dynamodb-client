<?php

declare(strict_types=1);

namespace Imper86\DynamoDBClient\Model;

/**
 * The description of a backup: what the backup itself is, and what the table looked like when it
 * was taken.
 */
final class BackupDescription
{
    public function __construct(
        public readonly ?BackupDetails $backupDetails = null,
        public readonly ?SourceTableDetails $sourceTableDetails = null,
        public readonly ?SourceTableFeatureDetails $sourceTableFeatureDetails = null,
    ) {}
}
