<?php

declare(strict_types=1);

namespace Imper86\DynamoDBClient\Model;

final readonly class ItemResponse
{
    public function __construct(
        public ?AttributeValueMap $item = null,
    ) {}
}
