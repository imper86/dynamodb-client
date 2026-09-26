<?php

declare(strict_types=1);

namespace Imper86\DynamoDBClient\Model;

/**
 * The properties of a local secondary index as they were when the backup was created.
 */
final class LocalSecondaryIndexInfo
{
    public function __construct(
        public readonly ?string $indexName = null,
        public readonly ?KeySchemaElementList $keySchema = null,
        public readonly ?Projection $projection = null,
    ) {}
}
