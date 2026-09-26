<?php

declare(strict_types=1);

namespace Imper86\DynamoDBClient\Model;

final class BatchStatementError
{
    public function __construct(
        public readonly ?BatchStatementErrorCode $code = null,
        public readonly ?AttributeValueMap $item = null,
        public readonly ?string $message = null,
    ) {}
}
