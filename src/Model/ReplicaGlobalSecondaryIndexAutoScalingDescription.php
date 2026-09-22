<?php

declare(strict_types=1);

namespace Imper86\DynamoDBClient\Model;

final readonly class ReplicaGlobalSecondaryIndexAutoScalingDescription
{
    public function __construct(
        public ?string $indexName = null,
        public ?IndexStatus $indexStatus = null,
        public ?AutoScalingSettingsDescription $provisionedReadCapacityAutoScalingSettings = null,
        public ?AutoScalingSettingsDescription $provisionedWriteCapacityAutoScalingSettings = null,
    ) {}
}
