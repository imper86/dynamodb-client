<?php

declare(strict_types=1);

namespace Imper86\DynamoDBClient\Message;

use Imper86\DynamoDBClient\Model\TableAutoScalingDescription;

final class UpdateTableReplicaAutoScalingResponse
{
    public function __construct(
        public readonly ?TableAutoScalingDescription $tableAutoScalingDescription = null,
    ) {}
}
