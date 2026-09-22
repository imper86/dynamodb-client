<?php

declare(strict_types=1);

namespace Imper86\DynamoDBClient\Model;

final readonly class GlobalTableWitnessDescription
{
    public function __construct(
        public ?string $regionName = null,
        public ?WitnessStatus $witnessStatus = null,
    ) {}
}
