<?php

declare(strict_types=1);

namespace Imper86\DynamoDBClient\Model;

final readonly class ReplicaAutoScalingDescription
{
    public function __construct(
        public ?ReplicaGlobalSecondaryIndexAutoScalingDescriptionList $globalSecondaryIndexes = null,
        public ?string $regionName = null,
        public ?AutoScalingSettingsDescription $replicaProvisionedReadCapacityAutoScalingSettings = null,
        public ?AutoScalingSettingsDescription $replicaProvisionedWriteCapacityAutoScalingSettings = null,
        public ?ReplicaStatus $replicaStatus = null,
    ) {}
}
