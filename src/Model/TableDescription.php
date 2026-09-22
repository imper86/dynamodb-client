<?php

declare(strict_types=1);

namespace Imper86\DynamoDBClient\Model;

use DateTimeImmutable;
use Symfony\Component\Serializer\Attribute\SerializedName;

final readonly class TableDescription
{
    public function __construct(
        public ?ArchivalSummary $archivalSummary = null,
        public ?AttributeDefinitionList $attributeDefinitions = null,
        public ?BillingModeSummary $billingModeSummary = null,
        public ?DateTimeImmutable $creationDateTime = null,
        public ?bool $deletionProtectionEnabled = null,
        public ?GlobalSecondaryIndexDescriptionList $globalSecondaryIndexes = null,
        public ?GlobalTableSettingsReplicationMode $globalTableSettingsReplicationMode = null,
        public ?string $globalTableVersion = null,
        public ?GlobalTableWitnessDescriptionList $globalTableWitnesses = null,
        public ?int $itemCount = null,
        public ?KeySchemaElementList $keySchema = null,
        public ?string $latestStreamArn = null,
        public ?string $latestStreamLabel = null,
        public ?LocalSecondaryIndexDescriptionList $localSecondaryIndexes = null,
        public ?MultiRegionConsistency $multiRegionConsistency = null,
        public ?OnDemandThroughput $onDemandThroughput = null,
        public ?ProvisionedThroughputDescription $provisionedThroughput = null,
        public ?ReplicaDescriptionList $replicas = null,
        public ?RestoreSummary $restoreSummary = null,
        #[SerializedName('SSEDescription')]
        public ?SSEDescription $sseDescription = null,
        public ?StreamSpecification $streamSpecification = null,
        public ?string $tableArn = null,
        public ?TableClassSummary $tableClassSummary = null,
        public ?string $tableId = null,
        public ?string $tableName = null,
        public ?int $tableSizeBytes = null,
        public ?TableStatus $tableStatus = null,
        public ?VectorIndexDescriptionList $vectorIndexes = null,
        public ?TableWarmThroughputDescription $warmThroughput = null,
    ) {}
}
