<?php

declare(strict_types=1);

namespace Imper86\DynamoDBClient\Model;

use DateTimeImmutable;
use Symfony\Component\Serializer\Attribute\SerializedName;

final readonly class ReplicaDescription
{
    public function __construct(
        public ?ReplicaGlobalSecondaryIndexDescriptionList $globalSecondaryIndexes = null,
        public ?GlobalTableSettingsReplicationMode $globalTableSettingsReplicationMode = null,
        #[SerializedName('KMSMasterKeyId')]
        public ?string $kmsMasterKeyId = null,
        public ?OnDemandThroughputOverride $onDemandThroughputOverride = null,
        public ?ProvisionedThroughputOverride $provisionedThroughputOverride = null,
        public ?string $regionName = null,
        public ?string $replicaArn = null,
        public ?DateTimeImmutable $replicaInaccessibleDateTime = null,
        public ?ReplicaStatus $replicaStatus = null,
        public ?string $replicaStatusDescription = null,
        public ?string $replicaStatusPercentProgress = null,
        public ?TableClassSummary $replicaTableClassSummary = null,
        public ?TableWarmThroughputDescription $warmThroughput = null,
    ) {}
}
