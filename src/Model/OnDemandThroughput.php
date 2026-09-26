<?php

declare(strict_types=1);

namespace Imper86\DynamoDBClient\Model;

final class OnDemandThroughput
{
    /**
     * The maximum throughput an on-demand table may reach. A value of -1 lifts the limit again.
     */
    public function __construct(
        public readonly ?int $maxReadRequestUnits = null,
        public readonly ?int $maxWriteRequestUnits = null,
    ) {}
}
