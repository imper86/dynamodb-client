<?php

declare(strict_types=1);

namespace Imper86\DynamoDBClient\Model;

use DateTimeImmutable;

/**
 * The details of the table as they were when the backup was created.
 */
final class SourceTableDetails
{
    /**
     * @param null|int $itemCount the approximate number of items in the table
     * @param null|int $tableSizeBytes the approximate size of the table
     */
    public function __construct(
        public readonly ?BillingMode $billingMode = null,
        public readonly ?int $itemCount = null,
        public readonly ?KeySchemaElementList $keySchema = null,
        public readonly ?OnDemandThroughput $onDemandThroughput = null,
        public readonly ?ProvisionedThroughput $provisionedThroughput = null,
        public readonly ?string $tableArn = null,
        public readonly ?DateTimeImmutable $tableCreationDateTime = null,
        public readonly ?string $tableId = null,
        public readonly ?string $tableName = null,
        public readonly ?int $tableSizeBytes = null,
    ) {}
}
