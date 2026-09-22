<?php

declare(strict_types=1);

namespace Imper86\DynamoDBClient\Model;

use DateTimeImmutable;

final readonly class RestoreSummary
{
    public function __construct(
        public ?DateTimeImmutable $restoreDateTime = null,
        public ?bool $restoreInProgress = null,
        public ?string $sourceBackupArn = null,
        public ?string $sourceTableArn = null,
    ) {}
}
