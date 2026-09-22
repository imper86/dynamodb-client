<?php

declare(strict_types=1);

namespace Imper86\DynamoDBClient\Model;

use DateTimeImmutable;

final readonly class ArchivalSummary
{
    public function __construct(
        public ?string $archivalBackupArn = null,
        public ?DateTimeImmutable $archivalDateTime = null,
        public ?string $archivalReason = null,
    ) {}
}
