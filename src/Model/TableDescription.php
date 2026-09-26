<?php

declare(strict_types=1);

namespace Imper86\DynamoDBClient\Model;

use DateTimeImmutable;
use Symfony\Component\Serializer\Attribute\SerializedName;

final class TableDescription
{
    public function __construct(
        public readonly ?ArchivalSummary $archivalSummary = null,
        public readonly ?AttributeDefinitionList $attributeDefinitions = null,
        public readonly ?BillingModeSummary $billingModeSummary = null,
        public readonly ?DateTimeImmutable $creationDateTime = null,
        public readonly ?bool $deletionProtectionEnabled = null,
        public readonly ?GlobalSecondaryIndexDescriptionList $globalSecondaryIndexes = null,
        public readonly ?GlobalTableSettingsReplicationMode $globalTableSettingsReplicationMode = null,
        public readonly ?string $globalTableVersion = null,
        public readonly ?GlobalTableWitnessDescriptionList $globalTableWitnesses = null,
        public readonly ?int $itemCount = null,
        public readonly ?KeySchemaElementList $keySchema = null,
        public readonly ?string $latestStreamArn = null,
        public readonly ?string $latestStreamLabel = null,
        public readonly ?LocalSecondaryIndexDescriptionList $localSecondaryIndexes = null,
        public readonly ?MultiRegionConsistency $multiRegionConsistency = null,
        public readonly ?OnDemandThroughput $onDemandThroughput = null,
        public readonly ?ProvisionedThroughputDescription $provisionedThroughput = null,
        public readonly ?ReplicaDescriptionList $replicas = null,
        public readonly ?RestoreSummary $restoreSummary = null,
        #[SerializedName('SSEDescription')]
        public readonly ?SSEDescription $sseDescription = null,
        public readonly ?StreamSpecification $streamSpecification = null,
        public readonly ?string $tableArn = null,
        public readonly ?TableClassSummary $tableClassSummary = null,
        public readonly ?string $tableId = null,
        public readonly ?string $tableName = null,
        public readonly ?int $tableSizeBytes = null,
        public readonly ?TableStatus $tableStatus = null,
        public readonly ?VectorIndexDescriptionList $vectorIndexes = null,
        public readonly ?TableWarmThroughputDescription $warmThroughput = null,
    ) {}
}
