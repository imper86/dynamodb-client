<?php

declare(strict_types=1);

namespace Imper86\DynamoDBClient\Message;

use Imper86\DynamoDBClient\Model\BackupDetails;

final readonly class CreateBackupResponse
{
    /**
     * DynamoDB answers every successful call with the details of the backup it started, but they are
     * the whole payload: requiring them would turn an unexpectedly empty body into a deserialization
     * failure instead of a response the caller can inspect.
     */
    public function __construct(
        public ?BackupDetails $backupDetails = null,
    ) {}
}
