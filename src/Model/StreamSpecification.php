<?php

declare(strict_types=1);

namespace Imper86\DynamoDBClient\Model;

final readonly class StreamSpecification
{
    public function __construct(
        public bool $streamEnabled,
        public ?StreamViewType $streamViewType = null,
    ) {}
}
