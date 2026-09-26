<?php

declare(strict_types=1);

namespace Imper86\DynamoDBClient\Model;

final class GlobalTableWitnessDescription
{
    public function __construct(
        public readonly ?string $regionName = null,
        public readonly ?WitnessStatus $witnessStatus = null,
    ) {}
}
