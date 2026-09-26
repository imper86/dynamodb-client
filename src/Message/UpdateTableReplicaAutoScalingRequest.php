<?php

declare(strict_types=1);

namespace Imper86\DynamoDBClient\Message;

use Imper86\DynamoDBClient\Model\AutoScalingSettingsUpdate;
use Imper86\DynamoDBClient\Model\GlobalSecondaryIndexAutoScalingUpdateList;
use Imper86\DynamoDBClient\Model\ReplicaAutoScalingUpdateList;
use InvalidArgumentException;
use Webmozart\Assert\Assert;

final class UpdateTableReplicaAutoScalingRequest
{
    /**
     * @param non-empty-string $tableName the global table name or its ARN
     * @throws InvalidArgumentException
     */
    public function __construct(
        public readonly string $tableName,
        public readonly ?GlobalSecondaryIndexAutoScalingUpdateList $globalSecondaryIndexUpdates = null,
        public readonly ?AutoScalingSettingsUpdate $provisionedWriteCapacityAutoScalingUpdate = null,
        public readonly ?ReplicaAutoScalingUpdateList $replicaUpdates = null,
    ) {
        Assert::stringNotEmpty($this->tableName);
        Assert::maxLength($this->tableName, 1024);
        Assert::nullOrMinCount($this->globalSecondaryIndexUpdates, 1);
        Assert::nullOrMinCount($this->replicaUpdates, 1);
    }
}
