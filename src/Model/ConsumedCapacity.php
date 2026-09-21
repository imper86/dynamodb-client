<?php

declare(strict_types=1);

namespace Imper86\DynamoDBClient\Model;

final readonly class ConsumedCapacity
{
    public function __construct(
        public ?float $capacityUnits = null,
        public ?ConsumedCapacityObjectMap $globalSecondaryIndexes = null,
        public ?ConsumedCapacityObjectMap $localSecondaryIndexes = null,
        public ?float $readCapacityUnits = null,
        public ?string $table = null,
        public ?string $tableName = null,
    ) {}
}
