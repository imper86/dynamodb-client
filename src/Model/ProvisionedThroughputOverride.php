<?php

declare(strict_types=1);

namespace Imper86\DynamoDBClient\Model;

final readonly class ProvisionedThroughputOverride
{
    public function __construct(
        public ?int $readCapacityUnits = null,
    ) {}
}
