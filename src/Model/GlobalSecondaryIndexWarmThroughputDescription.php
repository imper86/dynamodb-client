<?php

declare(strict_types=1);

namespace Imper86\DynamoDBClient\Model;

final readonly class GlobalSecondaryIndexWarmThroughputDescription
{
    public function __construct(
        public ?int $readUnitsPerSecond = null,
        public ?IndexStatus $status = null,
        public ?int $writeUnitsPerSecond = null,
    ) {}
}
