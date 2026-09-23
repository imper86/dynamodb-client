<?php

declare(strict_types=1);

namespace Imper86\DynamoDBClientTests\Model;

use InvalidArgumentException;
use Imper86\DynamoDBClient\Model\CreateGlobalSecondaryIndexAction;
use Imper86\DynamoDBClient\Model\DeleteGlobalSecondaryIndexAction;
use Imper86\DynamoDBClient\Model\GlobalSecondaryIndexUpdate;
use Imper86\DynamoDBClient\Model\KeySchemaElement;
use Imper86\DynamoDBClient\Model\KeySchemaElementList;
use Imper86\DynamoDBClient\Model\KeyType;
use Imper86\DynamoDBClient\Model\OnDemandThroughput;
use Imper86\DynamoDBClient\Model\Projection;
use Imper86\DynamoDBClient\Model\ProjectionType;
use Imper86\DynamoDBClient\Model\ProvisionedThroughput;
use Imper86\DynamoDBClient\Model\UpdateGlobalSecondaryIndexAction;
use Imper86\DynamoDBClient\Model\WarmThroughput;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(GlobalSecondaryIndexUpdate::class)]
final class GlobalSecondaryIndexUpdateTest extends TestCase
{
    /**
     * @throws InvalidArgumentException
     */
    public function testBuildsACreate(): void
    {
        $keySchema = new KeySchemaElementList([new KeySchemaElement('LastPostedBy', KeyType::HASH)]);
        $projection = new Projection(projectionType: ProjectionType::KEYS_ONLY);
        $onDemandThroughput = new OnDemandThroughput(maxReadRequestUnits: 100);
        $provisionedThroughput = new ProvisionedThroughput(5, 5);
        $warmThroughput = new WarmThroughput(readUnitsPerSecond: 12000);

        $update = GlobalSecondaryIndexUpdate::create(
            'LastPostedByIndex',
            $keySchema,
            $projection,
            $onDemandThroughput,
            $provisionedThroughput,
            $warmThroughput,
        );

        $create = $update->create;

        self::assertInstanceOf(CreateGlobalSecondaryIndexAction::class, $create);
        self::assertSame('LastPostedByIndex', $create->indexName);
        self::assertSame($keySchema, $create->keySchema);
        self::assertSame($projection, $create->projection);
        self::assertSame($onDemandThroughput, $create->onDemandThroughput);
        self::assertSame($provisionedThroughput, $create->provisionedThroughput);
        self::assertSame($warmThroughput, $create->warmThroughput);
        self::assertNull($update->delete);
        self::assertNull($update->update);
    }

    /**
     * @throws InvalidArgumentException
     */
    public function testBuildsADelete(): void
    {
        $update = GlobalSecondaryIndexUpdate::delete('ObsoleteIndex');

        self::assertSame('ObsoleteIndex', $update->delete?->indexName);
        self::assertNull($update->create);
        self::assertNull($update->update);
    }

    /**
     * @throws InvalidArgumentException
     */
    public function testBuildsAnUpdate(): void
    {
        $onDemandThroughput = new OnDemandThroughput(maxWriteRequestUnits: 50);
        $provisionedThroughput = new ProvisionedThroughput(10, 10);
        $warmThroughput = new WarmThroughput(writeUnitsPerSecond: 4000);

        $update = GlobalSecondaryIndexUpdate::update(
            'SubjectIndex',
            $onDemandThroughput,
            $provisionedThroughput,
            $warmThroughput,
        );

        $action = $update->update;

        self::assertInstanceOf(UpdateGlobalSecondaryIndexAction::class, $action);
        self::assertSame('SubjectIndex', $action->indexName);
        self::assertSame($onDemandThroughput, $action->onDemandThroughput);
        self::assertSame($provisionedThroughput, $action->provisionedThroughput);
        self::assertSame($warmThroughput, $action->warmThroughput);
        self::assertNull($update->create);
        self::assertNull($update->delete);
    }

    /**
     * @throws InvalidArgumentException
     */
    public function testRejectsAnIndexNameShorterThanTheServiceAllows(): void
    {
        $this->expectException(InvalidArgumentException::class);

        GlobalSecondaryIndexUpdate::delete('ix');
    }

    /**
     * @throws InvalidArgumentException
     */
    public function testRejectsAnIndexNameWithCharactersTheServiceDoesNotAllow(): void
    {
        $this->expectException(InvalidArgumentException::class);

        GlobalSecondaryIndexUpdate::update('Subject Index');
    }

    /**
     * @throws InvalidArgumentException
     */
    public function testRejectsACreateWithoutAKeySchema(): void
    {
        $this->expectException(InvalidArgumentException::class);

        GlobalSecondaryIndexUpdate::create(
            'LastPostedByIndex',
            new KeySchemaElementList(),
            new Projection(projectionType: ProjectionType::KEYS_ONLY),
        );
    }

    /**
     * @throws InvalidArgumentException
     */
    public function testRejectsAnUpdateWithoutAnyAction(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessageIsOrContains(
            'A GlobalSecondaryIndexUpdate needs exactly one of Create, Delete or Update.',
        );

        new GlobalSecondaryIndexUpdate();
    }

    /**
     * @throws InvalidArgumentException
     */
    public function testRejectsAnUpdateWithTwoActions(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessageIsOrContains(
            'A GlobalSecondaryIndexUpdate needs exactly one of Create, Delete or Update.',
        );

        new GlobalSecondaryIndexUpdate(
            delete: new DeleteGlobalSecondaryIndexAction('ObsoleteIndex'),
            update: new UpdateGlobalSecondaryIndexAction('SubjectIndex'),
        );
    }
}
