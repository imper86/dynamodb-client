<?php

declare(strict_types=1);

namespace Imper86\DynamoDBClient\Model;

final class ReplicaGlobalSecondaryIndexDescription
{
    public function __construct(
        public readonly ?string $indexName = null,
        public readonly ?OnDemandThroughputOverride $onDemandThroughputOverride = null,
        public readonly ?ProvisionedThroughputOverride $provisionedThroughputOverride = null,
        public readonly ?GlobalSecondaryIndexWarmThroughputDescription $warmThroughput = null,
    ) {}
}
