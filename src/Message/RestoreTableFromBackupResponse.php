<?php

declare(strict_types=1);

namespace Imper86\DynamoDBClient\Message;

use Imper86\DynamoDBClient\Model\TableDescription;

final class RestoreTableFromBackupResponse
{
    /**
     * The restore is asynchronous: the description that comes back reports a `TableStatus` of `CREATING`
     * and a `RestoreSummary` with `RestoreInProgress` set, and `DescribeTable` tells you when it is done.
     */
    public function __construct(
        public readonly ?TableDescription $tableDescription = null,
    ) {}
}
