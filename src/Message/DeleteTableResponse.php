<?php

declare(strict_types=1);

namespace Imper86\DynamoDBClient\Message;

use Imper86\DynamoDBClient\Model\TableDescription;

final readonly class DeleteTableResponse
{
    /**
     * `DeleteTable` is asynchronous: the description that comes back reports a `TableStatus` of
     * `DELETING`, and `DescribeTable` answers with a `ResourceNotFoundException` once the table is gone.
     */
    public function __construct(
        public ?TableDescription $tableDescription = null,
    ) {}
}
