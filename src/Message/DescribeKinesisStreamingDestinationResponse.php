<?php

declare(strict_types=1);

namespace Imper86\DynamoDBClient\Message;

use Imper86\DynamoDBClient\Model\KinesisDataStreamDestinationList;

final class DescribeKinesisStreamingDestinationResponse
{
    /**
     * The destination list is what the operation exists to return, so it defaults to empty rather than to
     * null: a table without destinations has nothing to report either way.
     *
     * @param null|string $tableName the name of the table described, even when the request gave its ARN
     */
    public function __construct(
        public readonly KinesisDataStreamDestinationList $kinesisDataStreamDestinations = new KinesisDataStreamDestinationList(),
        public readonly ?string $tableName = null,
    ) {}
}
