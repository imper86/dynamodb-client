<?php

declare(strict_types=1);

namespace Imper86\DynamoDBClient\Model;

use InvalidArgumentException;
use Webmozart\Assert\Assert;

final readonly class ReplicaAutoScalingUpdate
{
    /**
     * @param non-empty-string $regionName
     * @throws InvalidArgumentException
     */
    public function __construct(
        public string $regionName,
        public ?ReplicaGlobalSecondaryIndexAutoScalingUpdateList $replicaGlobalSecondaryIndexUpdates = null,
        public ?AutoScalingSettingsUpdate $replicaProvisionedReadCapacityAutoScalingUpdate = null,
    ) {
        Assert::stringNotEmpty($this->regionName);
    }
}
