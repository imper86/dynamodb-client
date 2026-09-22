<?php

declare(strict_types=1);

namespace Imper86\DynamoDBClient\Model;

/**
 * The properties of a local secondary index as they were when the backup was created.
 */
final readonly class LocalSecondaryIndexInfo
{
    public function __construct(
        public ?string $indexName = null,
        public ?KeySchemaElementList $keySchema = null,
        public ?Projection $projection = null,
    ) {}
}
