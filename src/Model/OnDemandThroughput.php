<?php

declare(strict_types=1);

namespace Imper86\DynamoDBClient\Model;

final readonly class OnDemandThroughput
{
    /**
     * The maximum throughput an on-demand table may reach. A value of -1 lifts the limit again.
     */
    public function __construct(
        public ?int $maxReadRequestUnits = null,
        public ?int $maxWriteRequestUnits = null,
    ) {}
}
