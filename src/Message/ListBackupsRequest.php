<?php

declare(strict_types=1);

namespace Imper86\DynamoDBClient\Message;

use DateTimeImmutable;
use Imper86\DynamoDBClient\Model\BackupTypeFilter;
use InvalidArgumentException;
use Webmozart\Assert\Assert;

final readonly class ListBackupsRequest
{
    /**
     * @param null|BackupTypeFilter $backupType which backups to list; DynamoDB lists USER backups by default
     * @param null|non-empty-string $exclusiveStartBackupArn the `LastEvaluatedBackupArn` of the previous page
     * @param null|positive-int $limit the maximum number of backups to return, at most 100
     * @param null|non-empty-string $tableName the name or ARN of the table to list the backups of
     * @param null|DateTimeImmutable $timeRangeLowerBound only backups created at or after this moment are listed
     * @param null|DateTimeImmutable $timeRangeUpperBound only backups created before this moment are listed
     * @throws InvalidArgumentException
     */
    public function __construct(
        public ?BackupTypeFilter $backupType = null,
        public ?string $exclusiveStartBackupArn = null,
        public ?int $limit = null,
        public ?string $tableName = null,
        public ?DateTimeImmutable $timeRangeLowerBound = null,
        public ?DateTimeImmutable $timeRangeUpperBound = null,
    ) {
        Assert::nullOrStringNotEmpty($this->exclusiveStartBackupArn);
        Assert::nullOrMinLength($this->exclusiveStartBackupArn, 37);
        Assert::nullOrMaxLength($this->exclusiveStartBackupArn, 1024);
        Assert::nullOrRange($this->limit, 1, 100);
        Assert::nullOrStringNotEmpty($this->tableName);
        Assert::nullOrMaxLength($this->tableName, 1024);
    }
}
