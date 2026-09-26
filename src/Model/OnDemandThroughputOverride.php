<?php

declare(strict_types=1);

namespace Imper86\DynamoDBClient\Model;

final class OnDemandThroughputOverride
{
    public function __construct(
        public readonly ?int $maxReadRequestUnits = null,
    ) {}
}
