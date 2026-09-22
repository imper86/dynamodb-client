<?php

declare(strict_types=1);

namespace Imper86\DynamoDBClient\Model;

final readonly class TableWarmThroughputDescription
{
    public function __construct(
        public ?int $readUnitsPerSecond = null,
        public ?TableStatus $status = null,
        public ?int $writeUnitsPerSecond = null,
    ) {}
}
