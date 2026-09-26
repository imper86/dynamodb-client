<?php

declare(strict_types=1);

namespace Imper86\DynamoDBClient\Model;

use InvalidArgumentException;
use Webmozart\Assert\Assert;

final class GlobalSecondaryIndex
{
    /**
     * @param non-empty-string $indexName
     * @throws InvalidArgumentException
     */
    public function __construct(
        public readonly string $indexName,
        public readonly KeySchemaElementList $keySchema,
        public readonly Projection $projection,
        public readonly ?OnDemandThroughput $onDemandThroughput = null,
        public readonly ?ProvisionedThroughput $provisionedThroughput = null,
        public readonly ?WarmThroughput $warmThroughput = null,
    ) {
        Assert::stringNotEmpty($this->indexName);
        Assert::minLength($this->indexName, 3);
        Assert::maxLength($this->indexName, 255);
        Assert::regex($this->indexName, '/^[a-zA-Z0-9_.\-]+$/');
        Assert::minCount($this->keySchema, 1);
    }
}
