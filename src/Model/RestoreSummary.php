<?php

declare(strict_types=1);

namespace Imper86\DynamoDBClient\Model;

use DateTimeImmutable;

final class RestoreSummary
{
    public function __construct(
        public readonly ?DateTimeImmutable $restoreDateTime = null,
        public readonly ?bool $restoreInProgress = null,
        public readonly ?string $sourceBackupArn = null,
        public readonly ?string $sourceTableArn = null,
    ) {}
}
