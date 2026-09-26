<?php

declare(strict_types=1);

namespace Imper86\DynamoDBClient\Model;

use InvalidArgumentException;
use Symfony\Component\Serializer\Attribute\SerializedName;
use Webmozart\Assert\Assert;

final class UpdateReplicationGroupMemberAction
{
    /**
     * @param non-empty-string $regionName
     * @param null|ReplicaGlobalSecondaryIndexList $globalSecondaryIndexes the indexes whose throughput differs
     *                                                                     from the source table in this Region
     * @param null|non-empty-string $kmsMasterKeyId the KMS key for the replica, when it differs from the default
     * @throws InvalidArgumentException
     */
    public function __construct(
        public readonly string $regionName,
        public readonly ?ReplicaGlobalSecondaryIndexList $globalSecondaryIndexes = null,
        #[SerializedName('KMSMasterKeyId')]
        public readonly ?string $kmsMasterKeyId = null,
        public readonly ?OnDemandThroughputOverride $onDemandThroughputOverride = null,
        public readonly ?ProvisionedThroughputOverride $provisionedThroughputOverride = null,
        public readonly ?TableClass $tableClassOverride = null,
    ) {
        Assert::stringNotEmpty($this->regionName);
        Assert::nullOrMinCount($this->globalSecondaryIndexes, 1);
        Assert::nullOrStringNotEmpty($this->kmsMasterKeyId);
    }
}
