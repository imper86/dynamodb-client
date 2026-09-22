<?php

declare(strict_types=1);

namespace Imper86\DynamoDBClient\Model;

final readonly class LocalSecondaryIndexDescription
{
    public function __construct(
        public ?string $indexArn = null,
        public ?string $indexName = null,
        public ?int $indexSizeBytes = null,
        public ?int $itemCount = null,
        public ?KeySchemaElementList $keySchema = null,
        public ?Projection $projection = null,
    ) {}
}
