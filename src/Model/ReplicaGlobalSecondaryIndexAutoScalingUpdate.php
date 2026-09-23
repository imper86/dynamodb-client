<?php

declare(strict_types=1);

namespace Imper86\DynamoDBClient\Model;

use InvalidArgumentException;
use Webmozart\Assert\Assert;

final readonly class ReplicaGlobalSecondaryIndexAutoScalingUpdate
{
    /**
     * @param null|non-empty-string $indexName
     * @throws InvalidArgumentException
     */
    public function __construct(
        public ?string $indexName = null,
        public ?AutoScalingSettingsUpdate $provisionedReadCapacityAutoScalingUpdate = null,
    ) {
        Assert::nullOrStringNotEmpty($this->indexName);
        Assert::nullOrMinLength($this->indexName, 3);
        Assert::nullOrMaxLength($this->indexName, 255);
        Assert::nullOrRegex($this->indexName, '/^[a-zA-Z0-9_.\-]+$/');
    }
}
