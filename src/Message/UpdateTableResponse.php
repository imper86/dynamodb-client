<?php

declare(strict_types=1);

namespace Imper86\DynamoDBClient\Message;

use Imper86\DynamoDBClient\Model\TableDescription;

final class UpdateTableResponse
{
    /**
     * `UpdateTable` is asynchronous: the description that comes back typically reports a `TableStatus` of
     * `UPDATING` until the change takes effect.
     */
    public function __construct(
        public readonly ?TableDescription $tableDescription = null,
    ) {}
}
