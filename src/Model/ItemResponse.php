<?php

declare(strict_types=1);

namespace Imper86\DynamoDBClient\Model;

final class ItemResponse
{
    public function __construct(
        public readonly ?AttributeValueMap $item = null,
    ) {}
}
