<?php

declare(strict_types=1);

namespace Imper86\DynamoDBClient\Model;

final class BatchStatementResponse
{
    public function __construct(
        public readonly ?BatchStatementError $error = null,
        public readonly ?AttributeValueMap $item = null,
        public readonly ?string $tableName = null,
    ) {}
}
