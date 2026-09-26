<?php

declare(strict_types=1);

namespace Imper86\DynamoDBClient\Message;

use Imper86\DynamoDBClient\Model\TableDescription;

final class CreateTableResponse
{
    /**
     * `CreateTable` is asynchronous: the description that comes back reports a `TableStatus` of
     * `CREATING`, and `DescribeTable` tells you when the table turned `ACTIVE`.
     */
    public function __construct(
        public readonly ?TableDescription $tableDescription = null,
    ) {}
}
