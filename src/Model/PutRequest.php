<?php

declare(strict_types=1);

namespace Imper86\DynamoDBClient\Model;

final class PutRequest
{
    public function __construct(
        public readonly AttributeValueMap $item,
    ) {}
}
