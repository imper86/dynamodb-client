<?php

declare(strict_types=1);

namespace Imper86\DynamoDBClient\Model;

use InvalidArgumentException;
use Webmozart\Assert\Assert;

final class VectorIndexUpdate
{
    /**
     * Exactly one of the two actions must be given.
     *
     * @throws InvalidArgumentException
     */
    public function __construct(
        public readonly ?CreateVectorIndexAction $create = null,
        public readonly ?DeleteVectorIndexAction $delete = null,
    ) {
        Assert::true(
            (!$this->create instanceof CreateVectorIndexAction) !== (!$this->delete instanceof DeleteVectorIndexAction),
            'A VectorIndexUpdate needs exactly one of Create or Delete.',
        );
    }

    /**
     * @param positive-int $dimensions
     * @param non-empty-string $indexName
     * @param non-empty-string $vectorAttributeName the attribute that holds the vectors to index
     * @throws InvalidArgumentException
     */
    public static function create(
        int $dimensions,
        VectorDistanceFunction $distanceFunction,
        string $indexName,
        Projection $projection,
        string $vectorAttributeName,
        ?SearchSchemaElementList $searchSchema = null,
    ): self {
        return new self(create: new CreateVectorIndexAction(
            dimensions: $dimensions,
            distanceFunction: $distanceFunction,
            indexName: $indexName,
            projection: $projection,
            vectorAttribute: new VectorAttributeDefinition($vectorAttributeName),
            searchSchema: $searchSchema,
        ));
    }

    /**
     * @param non-empty-string $indexName
     * @throws InvalidArgumentException
     */
    public static function delete(string $indexName): self
    {
        return new self(delete: new DeleteVectorIndexAction($indexName));
    }
}
