<?php

declare(strict_types=1);

namespace Imper86\DynamoDBClient\Model;

final class StreamSpecification
{
    public function __construct(
        public readonly bool $streamEnabled,
        public readonly ?StreamViewType $streamViewType = null,
    ) {}
}
