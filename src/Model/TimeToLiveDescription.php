<?php

declare(strict_types=1);

namespace Imper86\DynamoDBClient\Model;

final class TimeToLiveDescription
{
    public function __construct(
        public readonly ?string $attributeName = null,
        public readonly ?TimeToLiveStatus $timeToLiveStatus = null,
    ) {}
}
