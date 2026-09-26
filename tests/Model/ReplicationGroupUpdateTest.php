<?php

declare(strict_types=1);

namespace Imper86\DynamoDBClientTests\Model;

use InvalidArgumentException;
use Imper86\DynamoDBClient\Model\CreateReplicationGroupMemberAction;
use Imper86\DynamoDBClient\Model\DeleteReplicationGroupMemberAction;
use Imper86\DynamoDBClient\Model\OnDemandThroughputOverride;
use Imper86\DynamoDBClient\Model\ProvisionedThroughputOverride;
use Imper86\DynamoDBClient\Model\ReplicaGlobalSecondaryIndex;
use Imper86\DynamoDBClient\Model\ReplicaGlobalSecondaryIndexList;
use Imper86\DynamoDBClient\Model\ReplicationGroupUpdate;
use Imper86\DynamoDBClient\Model\TableClass;
use Imper86\DynamoDBClient\Model\UpdateReplicationGroupMemberAction;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(ReplicationGroupUpdate::class)]
final class ReplicationGroupUpdateTest extends TestCase
{
    /**
     * @throws InvalidArgumentException
     */
    public function testBuildsACreate(): void
    {
        $indexes = new ReplicaGlobalSecondaryIndexList([new ReplicaGlobalSecondaryIndex('SubjectIndex')]);
        $onDemandThroughputOverride = new OnDemandThroughputOverride(100);
        $provisionedThroughputOverride = new ProvisionedThroughputOverride(7);

        $update = ReplicationGroupUpdate::create(
            'eu-west-1',
            $indexes,
            'alias/replica-key',
            $onDemandThroughputOverride,
            $provisionedThroughputOverride,
            TableClass::STANDARD_INFREQUENT_ACCESS,
        );

        $create = $update->create;

        self::assertInstanceOf(CreateReplicationGroupMemberAction::class, $create);
        self::assertSame('eu-west-1', $create->regionName);
        self::assertSame($indexes, $create->globalSecondaryIndexes);
        self::assertSame('alias/replica-key', $create->kmsMasterKeyId);
        self::assertSame($onDemandThroughputOverride, $create->onDemandThroughputOverride);
        self::assertSame($provisionedThroughputOverride, $create->provisionedThroughputOverride);
        self::assertSame(TableClass::STANDARD_INFREQUENT_ACCESS, $create->tableClassOverride);
        self::assertNull($update->delete);
        self::assertNull($update->update);
    }

    /**
     * @throws InvalidArgumentException
     */
    public function testBuildsADelete(): void
    {
        $update = ReplicationGroupUpdate::delete('ap-south-1');

        self::assertSame('ap-south-1', $update->delete?->regionName);
        self::assertNull($update->create);
        self::assertNull($update->update);
    }

    /**
     * @throws InvalidArgumentException
     */
    public function testBuildsAnUpdate(): void
    {
        $indexes = new ReplicaGlobalSecondaryIndexList([new ReplicaGlobalSecondaryIndex('SubjectIndex')]);
        $onDemandThroughputOverride = new OnDemandThroughputOverride(100);
        $provisionedThroughputOverride = new ProvisionedThroughputOverride(7);

        $update = ReplicationGroupUpdate::update(
            'us-east-1',
            $indexes,
            'alias/replica-key',
            $onDemandThroughputOverride,
            $provisionedThroughputOverride,
            TableClass::STANDARD,
        );

        $action = $update->update;

        self::assertInstanceOf(UpdateReplicationGroupMemberAction::class, $action);
        self::assertSame('us-east-1', $action->regionName);
        self::assertSame($indexes, $action->globalSecondaryIndexes);
        self::assertSame('alias/replica-key', $action->kmsMasterKeyId);
        self::assertSame($onDemandThroughputOverride, $action->onDemandThroughputOverride);
        self::assertSame($provisionedThroughputOverride, $action->provisionedThroughputOverride);
        self::assertSame(TableClass::STANDARD, $action->tableClassOverride);
        self::assertNull($update->create);
        self::assertNull($update->delete);
    }

    /**
     * @throws InvalidArgumentException
     */
    public function testRejectsAnEmptyListOfIndexes(): void
    {
        $this->expectException(InvalidArgumentException::class);

        ReplicationGroupUpdate::create('eu-west-1', new ReplicaGlobalSecondaryIndexList());
    }

    /**
     * @throws InvalidArgumentException
     */
    public function testRejectsAReplicaIndexNameShorterThanTheServiceAllows(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new ReplicaGlobalSecondaryIndex('ix');
    }

    /**
     * @throws InvalidArgumentException
     */
    public function testRejectsAnUpdateWithoutAnyAction(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessageMatches('/A ReplicationGroupUpdate needs exactly one of Create, Delete or Update\./');

        new ReplicationGroupUpdate();
    }

    /**
     * @throws InvalidArgumentException
     */
    public function testRejectsAnUpdateWithTwoActions(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessageMatches('/A ReplicationGroupUpdate needs exactly one of Create, Delete or Update\./');

        new ReplicationGroupUpdate(
            create: new CreateReplicationGroupMemberAction('eu-west-1'),
            delete: new DeleteReplicationGroupMemberAction('ap-south-1'),
        );
    }
}
