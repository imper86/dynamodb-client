<?php

declare(strict_types=1);

namespace Imper86\DynamoDBClient\Model;

use DateTimeImmutable;

/**
 * The details of the table as they were when the backup was created.
 */
final readonly class SourceTableDetails
{
    /**
     * @param null|int $itemCount the approximate number of items in the table
     * @param null|int $tableSizeBytes the approximate size of the table
     */
    public function __construct(
        public ?BillingMode $billingMode = null,
        public ?int $itemCount = null,
        public ?KeySchemaElementList $keySchema = null,
        public ?OnDemandThroughput $onDemandThroughput = null,
        public ?ProvisionedThroughput $provisionedThroughput = null,
        public ?string $tableArn = null,
        public ?DateTimeImmutable $tableCreationDateTime = null,
        public ?string $tableId = null,
        public ?string $tableName = null,
        public ?int $tableSizeBytes = null,
    ) {}
}
