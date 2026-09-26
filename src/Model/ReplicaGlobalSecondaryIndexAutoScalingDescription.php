<?php

declare(strict_types=1);

namespace Imper86\DynamoDBClient\Model;

final class ReplicaGlobalSecondaryIndexAutoScalingDescription
{
    public function __construct(
        public readonly ?string $indexName = null,
        public readonly ?IndexStatus $indexStatus = null,
        public readonly ?AutoScalingSettingsDescription $provisionedReadCapacityAutoScalingSettings = null,
        public readonly ?AutoScalingSettingsDescription $provisionedWriteCapacityAutoScalingSettings = null,
    ) {}
}
