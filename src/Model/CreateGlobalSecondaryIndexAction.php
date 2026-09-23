<?php

declare(strict_types=1);

namespace Imper86\DynamoDBClient\Model;

use InvalidArgumentException;
use Webmozart\Assert\Assert;

final readonly class CreateGlobalSecondaryIndexAction
{
    /**
     * @param non-empty-string $indexName
     * @throws InvalidArgumentException
     */
    public function __construct(
        public string $indexName,
        public KeySchemaElementList $keySchema,
        public Projection $projection,
        public ?OnDemandThroughput $onDemandThroughput = null,
        public ?ProvisionedThroughput $provisionedThroughput = null,
        public ?WarmThroughput $warmThroughput = null,
    ) {
        Assert::stringNotEmpty($this->indexName);
        Assert::minLength($this->indexName, 3);
        Assert::maxLength($this->indexName, 255);
        Assert::regex($this->indexName, '/^[a-zA-Z0-9_.\-]+$/');
        Assert::minCount($this->keySchema, 1);
    }
}
