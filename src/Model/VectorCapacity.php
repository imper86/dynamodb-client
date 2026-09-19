<?php

declare(strict_types=1);

namespace OoAws\DynamoDBClient\Model;

final readonly class VectorCapacity
{
    public function __construct(
        public ?float $vectorSearchRequestBytes = null,
        public ?float $vectorWriteRequestBytes = null,
    ) {}
}
