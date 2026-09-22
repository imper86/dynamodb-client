<?php

declare(strict_types=1);

namespace Imper86\DynamoDBClient\Model;

final readonly class WarmThroughput
{
    public function __construct(
        public ?int $readUnitsPerSecond = null,
        public ?int $writeUnitsPerSecond = null,
    ) {}
}
