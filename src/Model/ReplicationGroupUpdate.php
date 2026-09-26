<?php

declare(strict_types=1);

namespace Imper86\DynamoDBClient\Model;

use InvalidArgumentException;
use Webmozart\Assert\Assert;

use function array_filter;
use function count;

final class ReplicationGroupUpdate
{
    /**
     * Exactly one of the three actions must be given.
     *
     * @throws InvalidArgumentException
     */
    public function __construct(
        public readonly ?CreateReplicationGroupMemberAction $create = null,
        public readonly ?DeleteReplicationGroupMemberAction $delete = null,
        public readonly ?UpdateReplicationGroupMemberAction $update = null,
    ) {
        Assert::same(
            count(array_filter(
                [$this->create, $this->delete, $this->update],
                static fn(?object $action): bool => null !== $action,
            )),
            1,
            'A ReplicationGroupUpdate needs exactly one of Create, Delete or Update.',
        );
    }

    /**
     * @param non-empty-string $regionName
     * @param null|non-empty-string $kmsMasterKeyId
     * @throws InvalidArgumentException
     */
    public static function create(
        string $regionName,
        ?ReplicaGlobalSecondaryIndexList $globalSecondaryIndexes = null,
        ?string $kmsMasterKeyId = null,
        ?OnDemandThroughputOverride $onDemandThroughputOverride = null,
        ?ProvisionedThroughputOverride $provisionedThroughputOverride = null,
        ?TableClass $tableClassOverride = null,
    ): self {
        return new self(create: new CreateReplicationGroupMemberAction(
            regionName: $regionName,
            globalSecondaryIndexes: $globalSecondaryIndexes,
            kmsMasterKeyId: $kmsMasterKeyId,
            onDemandThroughputOverride: $onDemandThroughputOverride,
            provisionedThroughputOverride: $provisionedThroughputOverride,
            tableClassOverride: $tableClassOverride,
        ));
    }

    /**
     * @param non-empty-string $regionName
     * @throws InvalidArgumentException
     */
    public static function delete(string $regionName): self
    {
        return new self(delete: new DeleteReplicationGroupMemberAction($regionName));
    }

    /**
     * @param non-empty-string $regionName
     * @param null|non-empty-string $kmsMasterKeyId
     * @throws InvalidArgumentException
     */
    public static function update(
        string $regionName,
        ?ReplicaGlobalSecondaryIndexList $globalSecondaryIndexes = null,
        ?string $kmsMasterKeyId = null,
        ?OnDemandThroughputOverride $onDemandThroughputOverride = null,
        ?ProvisionedThroughputOverride $provisionedThroughputOverride = null,
        ?TableClass $tableClassOverride = null,
    ): self {
        return new self(update: new UpdateReplicationGroupMemberAction(
            regionName: $regionName,
            globalSecondaryIndexes: $globalSecondaryIndexes,
            kmsMasterKeyId: $kmsMasterKeyId,
            onDemandThroughputOverride: $onDemandThroughputOverride,
            provisionedThroughputOverride: $provisionedThroughputOverride,
            tableClassOverride: $tableClassOverride,
        ));
    }
}
