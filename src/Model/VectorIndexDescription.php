<?php

declare(strict_types=1);

namespace Imper86\DynamoDBClient\Model;

final class VectorIndexDescription
{
    public function __construct(
        public readonly ?bool $backfilling = null,
        public readonly ?int $dimensions = null,
        public readonly ?VectorDistanceFunction $distanceFunction = null,
        public readonly ?string $indexArn = null,
        public readonly ?string $indexName = null,
        public readonly ?int $indexSizeBytes = null,
        public readonly ?IndexStatus $indexStatus = null,
        public readonly ?int $itemCount = null,
        public readonly ?Projection $projection = null,
        public readonly ?SearchSchemaElementList $searchSchema = null,
        public readonly ?VectorAttributeDefinition $vectorAttribute = null,
    ) {}
}
