<?php

declare(strict_types=1);

namespace Imper86\DynamoDBClient\Model;

final class ProvisionedThroughputOverride
{
    public function __construct(
        public readonly ?int $readCapacityUnits = null,
    ) {}
}
