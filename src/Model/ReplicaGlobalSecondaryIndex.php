<?php

declare(strict_types=1);

namespace Imper86\DynamoDBClient\Model;

use InvalidArgumentException;
use Webmozart\Assert\Assert;

final class ReplicaGlobalSecondaryIndex
{
    /**
     * @param non-empty-string $indexName
     * @throws InvalidArgumentException
     */
    public function __construct(
        public readonly string $indexName,
        public readonly ?OnDemandThroughputOverride $onDemandThroughputOverride = null,
        public readonly ?ProvisionedThroughputOverride $provisionedThroughputOverride = null,
    ) {
        Assert::stringNotEmpty($this->indexName);
        Assert::minLength($this->indexName, 3);
        Assert::maxLength($this->indexName, 255);
        Assert::regex($this->indexName, '/^[a-zA-Z0-9_.\-]+$/');
    }
}
