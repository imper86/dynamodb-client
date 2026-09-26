<?php

declare(strict_types=1);

namespace Imper86\DynamoDBClient\Model;

final class ConsumedCapacity
{
    public function __construct(
        public readonly ?float $capacityUnits = null,
        public readonly ?ConsumedCapacityMap $globalSecondaryIndexes = null,
        public readonly ?ConsumedCapacityMap $localSecondaryIndexes = null,
        public readonly ?float $readCapacityUnits = null,
        public readonly ?string $table = null,
        public readonly ?string $tableName = null,
    ) {}
}
