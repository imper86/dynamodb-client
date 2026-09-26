<?php

declare(strict_types=1);

namespace Imper86\DynamoDBClient\Model;

use InvalidArgumentException;
use Webmozart\Assert\Assert;

final class CreateVectorIndexAction
{
    /**
     * @param positive-int $dimensions
     * @param non-empty-string $indexName
     * @throws InvalidArgumentException
     */
    public function __construct(
        public readonly int $dimensions,
        public readonly VectorDistanceFunction $distanceFunction,
        public readonly string $indexName,
        public readonly Projection $projection,
        public readonly VectorAttributeDefinition $vectorAttribute,
        public readonly ?SearchSchemaElementList $searchSchema = null,
    ) {
        Assert::positiveInteger($this->dimensions);
        Assert::stringNotEmpty($this->indexName);
        Assert::minLength($this->indexName, 3);
        Assert::maxLength($this->indexName, 255);
        Assert::regex($this->indexName, '/^[a-zA-Z0-9_.\-]+$/');
        Assert::nullOrMinCount($this->searchSchema, 1);
    }
}
