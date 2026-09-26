<?php

declare(strict_types=1);

namespace Imper86\DynamoDBClient\Message;

final class DescribeLimitsResponse
{
    /**
     * @param null|int $accountMaxReadCapacityUnits the read capacity the account may provision across all its
     *                                              tables in the region
     * @param null|int $accountMaxWriteCapacityUnits the write capacity the account may provision across all its
     *                                               tables in the region
     * @param null|int $tableMaxReadCapacityUnits the read capacity a new table may be created with, its global
     *                                            secondary indexes included
     * @param null|int $tableMaxWriteCapacityUnits the write capacity a new table may be created with, its global
     *                                             secondary indexes included
     */
    public function __construct(
        public readonly ?int $accountMaxReadCapacityUnits = null,
        public readonly ?int $accountMaxWriteCapacityUnits = null,
        public readonly ?int $tableMaxReadCapacityUnits = null,
        public readonly ?int $tableMaxWriteCapacityUnits = null,
    ) {}
}
