<?php

declare(strict_types=1);

namespace Imper86\DynamoDBClient\Model;

final readonly class ReplicaGlobalSecondaryIndexDescription
{
    public function __construct(
        public ?string $indexName = null,
        public ?OnDemandThroughputOverride $onDemandThroughputOverride = null,
        public ?ProvisionedThroughputOverride $provisionedThroughputOverride = null,
        public ?GlobalSecondaryIndexWarmThroughputDescription $warmThroughput = null,
    ) {}
}
