<?php

declare(strict_types=1);

namespace Imper86\DynamoDBClient\Model;

final class WarmThroughput
{
    public function __construct(
        public readonly ?int $readUnitsPerSecond = null,
        public readonly ?int $writeUnitsPerSecond = null,
    ) {}
}
