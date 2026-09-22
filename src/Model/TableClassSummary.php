<?php

declare(strict_types=1);

namespace Imper86\DynamoDBClient\Model;

use DateTimeImmutable;

final readonly class TableClassSummary
{
    public function __construct(
        public ?DateTimeImmutable $lastUpdateDateTime = null,
        public ?TableClass $tableClass = null,
    ) {}
}
