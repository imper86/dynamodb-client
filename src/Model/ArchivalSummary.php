<?php

declare(strict_types=1);

namespace Imper86\DynamoDBClient\Model;

use DateTimeImmutable;

final class ArchivalSummary
{
    public function __construct(
        public readonly ?string $archivalBackupArn = null,
        public readonly ?DateTimeImmutable $archivalDateTime = null,
        public readonly ?string $archivalReason = null,
    ) {}
}
