<?php

declare(strict_types=1);

namespace Imper86\DynamoDBClient\Model;

final class VectorCapacity
{
    public function __construct(
        public readonly ?float $vectorSearchRequestBytes = null,
        public readonly ?float $vectorWriteRequestBytes = null,
    ) {}
}
