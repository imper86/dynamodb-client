<?php

declare(strict_types=1);

namespace Imper86\DynamoDBClient\Model;

final readonly class OnDemandThroughputOverride
{
    public function __construct(
        public ?int $maxReadRequestUnits = null,
    ) {}
}
