<?php

declare(strict_types=1);

namespace Imper86\DynamoDBClient\Model;

final class ReplicaAutoScalingDescription
{
    public function __construct(
        public readonly ?ReplicaGlobalSecondaryIndexAutoScalingDescriptionList $globalSecondaryIndexes = null,
        public readonly ?string $regionName = null,
        public readonly ?AutoScalingSettingsDescription $replicaProvisionedReadCapacityAutoScalingSettings = null,
        public readonly ?AutoScalingSettingsDescription $replicaProvisionedWriteCapacityAutoScalingSettings = null,
        public readonly ?ReplicaStatus $replicaStatus = null,
    ) {}
}
