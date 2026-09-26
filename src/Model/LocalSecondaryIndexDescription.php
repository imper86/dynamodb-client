<?php

declare(strict_types=1);

namespace Imper86\DynamoDBClient\Model;

final class LocalSecondaryIndexDescription
{
    public function __construct(
        public readonly ?string $indexArn = null,
        public readonly ?string $indexName = null,
        public readonly ?int $indexSizeBytes = null,
        public readonly ?int $itemCount = null,
        public readonly ?KeySchemaElementList $keySchema = null,
        public readonly ?Projection $projection = null,
    ) {}
}
