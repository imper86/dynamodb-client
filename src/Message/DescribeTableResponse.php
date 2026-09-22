<?php

declare(strict_types=1);

namespace Imper86\DynamoDBClient\Message;

use Imper86\DynamoDBClient\Model\TableDescription;

final readonly class DescribeTableResponse
{
    public function __construct(
        public ?TableDescription $table = null,
    ) {}
}
