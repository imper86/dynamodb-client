<?php

declare(strict_types=1);

namespace Imper86\DynamoDBClient\Model;

final readonly class TableAutoScalingDescription
{
    public function __construct(
        public ?ReplicaAutoScalingDescriptionList $replicas = null,
        public ?string $tableName = null,
        public ?TableStatus $tableStatus = null,
    ) {}
}
