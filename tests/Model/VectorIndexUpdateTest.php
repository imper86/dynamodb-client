<?php

declare(strict_types=1);

namespace Imper86\DynamoDBClientTests\Model;

use InvalidArgumentException;
use Imper86\DynamoDBClient\Model\CreateVectorIndexAction;
use Imper86\DynamoDBClient\Model\DeleteVectorIndexAction;
use Imper86\DynamoDBClient\Model\Projection;
use Imper86\DynamoDBClient\Model\ProjectionType;
use Imper86\DynamoDBClient\Model\SearchSchemaElement;
use Imper86\DynamoDBClient\Model\SearchSchemaElementList;
use Imper86\DynamoDBClient\Model\SearchSchemaElementType;
use Imper86\DynamoDBClient\Model\VectorDistanceFunction;
use Imper86\DynamoDBClient\Model\VectorIndexUpdate;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(VectorIndexUpdate::class)]
final class VectorIndexUpdateTest extends TestCase
{
    /**
     * @throws InvalidArgumentException
     */
    public function testBuildsACreate(): void
    {
        $projection = new Projection(projectionType: ProjectionType::ALL);
        $searchSchema = new SearchSchemaElementList([
            new SearchSchemaElement('ForumName', SearchSchemaElementType::HASH),
        ]);

        $update = VectorIndexUpdate::create(
            3,
            VectorDistanceFunction::COSINE,
            'EmbeddingIndex',
            $projection,
            'Embedding',
            $searchSchema,
        );

        $create = $update->create;

        self::assertInstanceOf(CreateVectorIndexAction::class, $create);
        self::assertSame(3, $create->dimensions);
        self::assertSame(VectorDistanceFunction::COSINE, $create->distanceFunction);
        self::assertSame('EmbeddingIndex', $create->indexName);
        self::assertSame($projection, $create->projection);
        self::assertSame('Embedding', $create->vectorAttribute->attributeName);
        self::assertSame($searchSchema, $create->searchSchema);
        self::assertNull($update->delete);
    }

    /**
     * @throws InvalidArgumentException
     */
    public function testBuildsADelete(): void
    {
        $update = VectorIndexUpdate::delete('EmbeddingIndex');

        self::assertSame('EmbeddingIndex', $update->delete?->indexName);
        self::assertNull($update->create);
    }

    /**
     * @throws InvalidArgumentException
     */
    public function testRejectsAnEmptySearchSchema(): void
    {
        $this->expectException(InvalidArgumentException::class);

        VectorIndexUpdate::create(
            3,
            VectorDistanceFunction::COSINE,
            'EmbeddingIndex',
            new Projection(projectionType: ProjectionType::ALL),
            'Embedding',
            new SearchSchemaElementList(),
        );
    }

    /**
     * @throws InvalidArgumentException
     */
    public function testRejectsAnIndexNameShorterThanTheServiceAllows(): void
    {
        $this->expectException(InvalidArgumentException::class);

        VectorIndexUpdate::delete('ix');
    }

    /**
     * @throws InvalidArgumentException
     */
    public function testRejectsAnUpdateWithoutAnyAction(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessageMatches('/A VectorIndexUpdate needs exactly one of Create or Delete\./');

        new VectorIndexUpdate();
    }

    /**
     * @throws InvalidArgumentException
     */
    public function testRejectsAnUpdateWithBothActions(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessageMatches('/A VectorIndexUpdate needs exactly one of Create or Delete\./');

        new VectorIndexUpdate(
            create: VectorIndexUpdate::create(
                3,
                VectorDistanceFunction::COSINE,
                'EmbeddingIndex',
                new Projection(projectionType: ProjectionType::ALL),
                'Embedding',
            )->create,
            delete: new DeleteVectorIndexAction('OldEmbeddingIndex'),
        );
    }
}
