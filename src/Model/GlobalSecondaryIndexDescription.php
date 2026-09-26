<?php

declare(strict_types=1);

namespace Imper86\DynamoDBClient\Model;

final class GlobalSecondaryIndexDescription
{
    public function __construct(
        public readonly ?bool $backfilling = null,
        public readonly ?string $indexArn = null,
        public readonly ?string $indexName = null,
        public readonly ?int $indexSizeBytes = null,
        public readonly ?IndexStatus $indexStatus = null,
        public readonly ?int $itemCount = null,
        public readonly ?KeySchemaElementList $keySchema = null,
        public readonly ?OnDemandThroughput $onDemandThroughput = null,
        public readonly ?Projection $projection = null,
        public readonly ?ProvisionedThroughputDescription $provisionedThroughput = null,
        public readonly ?GlobalSecondaryIndexWarmThroughputDescription $warmThroughput = null,
    ) {}
}
