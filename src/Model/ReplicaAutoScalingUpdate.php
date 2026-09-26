<?php

declare(strict_types=1);

namespace Imper86\DynamoDBClient\Model;

use InvalidArgumentException;
use Webmozart\Assert\Assert;

final class ReplicaAutoScalingUpdate
{
    /**
     * @param non-empty-string $regionName
     * @throws InvalidArgumentException
     */
    public function __construct(
        public readonly string $regionName,
        public readonly ?ReplicaGlobalSecondaryIndexAutoScalingUpdateList $replicaGlobalSecondaryIndexUpdates = null,
        public readonly ?AutoScalingSettingsUpdate $replicaProvisionedReadCapacityAutoScalingUpdate = null,
    ) {
        Assert::stringNotEmpty($this->regionName);
    }
}
