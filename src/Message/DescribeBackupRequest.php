<?php

declare(strict_types=1);

namespace Imper86\DynamoDBClient\Message;

use InvalidArgumentException;
use Webmozart\Assert\Assert;

final class DescribeBackupRequest
{
    /**
     * @param non-empty-string $backupArn the ARN of the backup to describe
     * @throws InvalidArgumentException
     */
    public function __construct(
        public readonly string $backupArn,
    ) {
        Assert::stringNotEmpty($this->backupArn);
        Assert::minLength($this->backupArn, 37);
        Assert::maxLength($this->backupArn, 1024);
    }
}
