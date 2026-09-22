<?php

declare(strict_types=1);

namespace Imper86\DynamoDBClient\Model;

/**
 * The configuration of a vector index as it was when the backup was created.
 */
final readonly class VectorIndexInfo
{
    public function __construct(
        public ?int $dimensions = null,
        public ?VectorDistanceFunction $distanceFunction = null,
        public ?string $indexName = null,
        public ?Projection $projection = null,
        public ?SearchSchemaElementList $searchSchema = null,
        public ?VectorAttributeDefinition $vectorAttribute = null,
    ) {}
}
