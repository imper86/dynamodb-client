<?php

declare(strict_types=1);

namespace Imper86\DynamoDBClient\Model;

final readonly class TimeToLiveDescription
{
    public function __construct(
        public ?string $attributeName = null,
        public ?TimeToLiveStatus $timeToLiveStatus = null,
    ) {}
}
