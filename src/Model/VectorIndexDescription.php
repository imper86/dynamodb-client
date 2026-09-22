<?php

declare(strict_types=1);

namespace Imper86\DynamoDBClient\Model;

final readonly class VectorIndexDescription
{
    public function __construct(
        public ?bool $backfilling = null,
        public ?int $dimensions = null,
        public ?VectorDistanceFunction $distanceFunction = null,
        public ?string $indexArn = null,
        public ?string $indexName = null,
        public ?int $indexSizeBytes = null,
        public ?IndexStatus $indexStatus = null,
        public ?int $itemCount = null,
        public ?Projection $projection = null,
        public ?SearchSchemaElementList $searchSchema = null,
        public ?VectorAttributeDefinition $vectorAttribute = null,
    ) {}
}
