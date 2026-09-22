<?php

declare(strict_types=1);

namespace Imper86\DynamoDBClient\Model;

use DateTimeImmutable;

final readonly class ProvisionedThroughputDescription
{
    public function __construct(
        public ?DateTimeImmutable $lastDecreaseDateTime = null,
        public ?DateTimeImmutable $lastIncreaseDateTime = null,
        public ?int $numberOfDecreasesToday = null,
        public ?int $readCapacityUnits = null,
        public ?int $writeCapacityUnits = null,
    ) {}
}
