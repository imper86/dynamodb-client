<?php

declare(strict_types=1);

namespace Imper86\DynamoDBClient\Model;

final readonly class BatchStatementError
{
    public function __construct(
        public ?BatchStatementErrorCode $code = null,
        public ?AttributeValueMap $item = null,
        public ?string $message = null,
    ) {}
}
