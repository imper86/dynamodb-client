<?php

declare(strict_types=1);

namespace Imper86\DynamoDBClient\Model;

use DateTimeImmutable;

final class TableClassSummary
{
    public function __construct(
        public readonly ?DateTimeImmutable $lastUpdateDateTime = null,
        public readonly ?TableClass $tableClass = null,
    ) {}
}
