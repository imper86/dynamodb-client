<?php

declare(strict_types=1);

namespace Imper86\DynamoDBClient\Model;

final readonly class Capacity
{
    public function __construct(
        public ?float $capacityUnits = null,
        public ?float $readCapacityUnits = null,
        public ?float $writeCapacityUnits = null,
    ) {}
}
