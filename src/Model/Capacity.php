<?php

declare(strict_types=1);

namespace Imper86\DynamoDBClient\Model;

final class Capacity
{
    public function __construct(
        public readonly ?float $capacityUnits = null,
        public readonly ?float $readCapacityUnits = null,
        public readonly ?float $writeCapacityUnits = null,
    ) {}
}
