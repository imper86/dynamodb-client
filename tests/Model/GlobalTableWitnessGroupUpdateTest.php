<?php

declare(strict_types=1);

namespace Imper86\DynamoDBClientTests\Model;

use InvalidArgumentException;
use Imper86\DynamoDBClient\Model\CreateGlobalTableWitnessGroupMemberAction;
use Imper86\DynamoDBClient\Model\DeleteGlobalTableWitnessGroupMemberAction;
use Imper86\DynamoDBClient\Model\GlobalTableWitnessGroupUpdate;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(GlobalTableWitnessGroupUpdate::class)]
final class GlobalTableWitnessGroupUpdateTest extends TestCase
{
    /**
     * @throws InvalidArgumentException
     */
    public function testBuildsACreate(): void
    {
        $update = GlobalTableWitnessGroupUpdate::create('us-east-2');

        self::assertSame('us-east-2', $update->create?->regionName);
        self::assertNull($update->delete);
    }

    /**
     * @throws InvalidArgumentException
     */
    public function testBuildsADelete(): void
    {
        $update = GlobalTableWitnessGroupUpdate::delete('us-east-2');

        self::assertSame('us-east-2', $update->delete?->regionName);
        self::assertNull($update->create);
    }

    /**
     * @throws InvalidArgumentException
     */
    public function testRejectsAnUpdateWithoutAnyAction(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessageIsOrContains(
            'A GlobalTableWitnessGroupUpdate needs exactly one of Create or Delete.',
        );

        new GlobalTableWitnessGroupUpdate();
    }

    /**
     * @throws InvalidArgumentException
     */
    public function testRejectsAnUpdateWithBothActions(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessageIsOrContains(
            'A GlobalTableWitnessGroupUpdate needs exactly one of Create or Delete.',
        );

        new GlobalTableWitnessGroupUpdate(
            create: new CreateGlobalTableWitnessGroupMemberAction('us-east-2'),
            delete: new DeleteGlobalTableWitnessGroupMemberAction('us-west-2'),
        );
    }
}
