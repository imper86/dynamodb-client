<?php

declare(strict_types=1);

namespace Imper86\DynamoDBClient\Model;

/**
 * The configuration of a vector index as it was when the backup was created.
 */
final class VectorIndexInfo
{
    public function __construct(
        public readonly ?int $dimensions = null,
        public readonly ?VectorDistanceFunction $distanceFunction = null,
        public readonly ?string $indexName = null,
        public readonly ?Projection $projection = null,
        public readonly ?SearchSchemaElementList $searchSchema = null,
        public readonly ?VectorAttributeDefinition $vectorAttribute = null,
    ) {}
}
