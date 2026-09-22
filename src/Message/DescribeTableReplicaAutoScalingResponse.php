<?php

declare(strict_types=1);

namespace Imper86\DynamoDBClient\Message;

use Imper86\DynamoDBClient\Model\TableAutoScalingDescription;

final readonly class DescribeTableReplicaAutoScalingResponse
{
    public function __construct(
        public ?TableAutoScalingDescription $tableAutoScalingDescription = null,
    ) {}
}
