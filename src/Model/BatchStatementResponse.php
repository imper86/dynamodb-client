<?php

declare(strict_types=1);

namespace Imper86\DynamoDBClient\Model;

final readonly class BatchStatementResponse
{
    public function __construct(
        public ?BatchStatementError $error = null,
        public ?AttributeValueMap $item = null,
        public ?string $tableName = null,
    ) {}
}
