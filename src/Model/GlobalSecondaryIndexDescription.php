<?php

declare(strict_types=1);

namespace Imper86\DynamoDBClient\Model;

final readonly class GlobalSecondaryIndexDescription
{
    public function __construct(
        public ?bool $backfilling = null,
        public ?string $indexArn = null,
        public ?string $indexName = null,
        public ?int $indexSizeBytes = null,
        public ?IndexStatus $indexStatus = null,
        public ?int $itemCount = null,
        public ?KeySchemaElementList $keySchema = null,
        public ?OnDemandThroughput $onDemandThroughput = null,
        public ?Projection $projection = null,
        public ?ProvisionedThroughputDescription $provisionedThroughput = null,
        public ?GlobalSecondaryIndexWarmThroughputDescription $warmThroughput = null,
    ) {}
}
