<?php

declare(strict_types=1);

namespace Imper86\DynamoDBClient\Message;

use Imper86\DynamoDBClient\Model\BackupSummaryList;

final class ListBackupsResponse
{
    /**
     * A page without backups answers with an empty `BackupSummaries` list, so it defaults to empty
     * instead of to null.
     *
     * @param null|string $lastEvaluatedBackupArn where the next page starts; absent on the last page
     */
    public function __construct(
        public readonly BackupSummaryList $backupSummaries = new BackupSummaryList(),
        public readonly ?string $lastEvaluatedBackupArn = null,
    ) {}
}
