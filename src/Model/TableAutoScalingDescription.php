<?php

declare(strict_types=1);

namespace Imper86\DynamoDBClient\Model;

final class TableAutoScalingDescription
{
    public function __construct(
        public readonly ?ReplicaAutoScalingDescriptionList $replicas = null,
        public readonly ?string $tableName = null,
        public readonly ?TableStatus $tableStatus = null,
    ) {}
}
