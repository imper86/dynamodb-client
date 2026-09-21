<?php

declare(strict_types=1);

namespace Imper86\DynamoDBClient\Model;

final readonly class DeleteRequest
{
    public function __construct(
        public AttributeValueMap $key,
    ) {}
}
