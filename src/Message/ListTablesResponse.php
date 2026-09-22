<?php

declare(strict_types=1);

namespace Imper86\DynamoDBClient\Message;

use Imper86\DynamoDBClient\ValueObject\StringList;

final readonly class ListTablesResponse
{
    /**
     * An account without tables answers with an empty `TableNames` list, so it defaults to empty instead
     * of to null.
     *
     * @param null|string $lastEvaluatedTableName where the next page starts; absent on the last page
     */
    public function __construct(
        public StringList $tableNames = new StringList(),
        public ?string $lastEvaluatedTableName = null,
    ) {}
}
