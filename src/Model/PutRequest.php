<?php

declare(strict_types=1);

namespace Imper86\DynamoDBClient\Model;

final readonly class PutRequest
{
    public function __construct(
        public AttributeValueMap $item,
    ) {}
}
