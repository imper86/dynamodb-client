<?php

declare(strict_types=1);

namespace Imper86\DynamoDBClient\Model;

use DateTimeImmutable;
use Symfony\Component\Serializer\Attribute\SerializedName;

final class ReplicaDescription
{
    public function __construct(
        public readonly ?ReplicaGlobalSecondaryIndexDescriptionList $globalSecondaryIndexes = null,
        public readonly ?GlobalTableSettingsReplicationMode $globalTableSettingsReplicationMode = null,
        #[SerializedName('KMSMasterKeyId')]
        public readonly ?string $kmsMasterKeyId = null,
        public readonly ?OnDemandThroughputOverride $onDemandThroughputOverride = null,
        public readonly ?ProvisionedThroughputOverride $provisionedThroughputOverride = null,
        public readonly ?string $regionName = null,
        public readonly ?string $replicaArn = null,
        public readonly ?DateTimeImmutable $replicaInaccessibleDateTime = null,
        public readonly ?ReplicaStatus $replicaStatus = null,
        public readonly ?string $replicaStatusDescription = null,
        public readonly ?string $replicaStatusPercentProgress = null,
        public readonly ?TableClassSummary $replicaTableClassSummary = null,
        public readonly ?TableWarmThroughputDescription $warmThroughput = null,
    ) {}
}
