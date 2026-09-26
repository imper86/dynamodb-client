<?php

declare(strict_types=1);

namespace Imper86\DynamoDBClient\Message;

use InvalidArgumentException;
use Webmozart\Assert\Assert;

final class CreateBackupRequest
{
    /**
     * @param non-empty-string $backupName
     * @param non-empty-string $tableName the name of the table to back up, or its ARN
     * @throws InvalidArgumentException
     */
    public function __construct(
        public readonly string $backupName,
        public readonly string $tableName,
    ) {
        Assert::stringNotEmpty($this->backupName);
        Assert::minLength($this->backupName, 3);
        Assert::maxLength($this->backupName, 255);
        Assert::regex($this->backupName, '/^[a-zA-Z0-9_.\-]+$/');
        Assert::stringNotEmpty($this->tableName);
        Assert::maxLength($this->tableName, 1024);
    }
}
