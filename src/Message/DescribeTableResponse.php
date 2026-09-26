<?php

declare(strict_types=1);

namespace Imper86\DynamoDBClient\Message;

use Imper86\DynamoDBClient\Model\TableDescription;

final class DescribeTableResponse
{
    public function __construct(
        public readonly ?TableDescription $table = null,
    ) {}
}
