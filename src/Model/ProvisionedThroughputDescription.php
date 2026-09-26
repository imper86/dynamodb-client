<?php

declare(strict_types=1);

namespace Imper86\DynamoDBClient\Model;

use DateTimeImmutable;

final class ProvisionedThroughputDescription
{
    public function __construct(
        public readonly ?DateTimeImmutable $lastDecreaseDateTime = null,
        public readonly ?DateTimeImmutable $lastIncreaseDateTime = null,
        public readonly ?int $numberOfDecreasesToday = null,
        public readonly ?int $readCapacityUnits = null,
        public readonly ?int $writeCapacityUnits = null,
    ) {}
}
