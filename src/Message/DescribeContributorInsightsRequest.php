<?php

declare(strict_types=1);

namespace Imper86\DynamoDBClient\Message;

use InvalidArgumentException;
use Webmozart\Assert\Assert;

final readonly class DescribeContributorInsightsRequest
{
    /**
     * @param non-empty-string $tableName the table name or its ARN
     * @param null|non-empty-string $indexName the global secondary index to describe instead of the table
     * @throws InvalidArgumentException
     */
    public function __construct(
        public string $tableName,
        public ?string $indexName = null,
    ) {
        Assert::stringNotEmpty($this->tableName);
        Assert::maxLength($this->tableName, 1024);
        Assert::nullOrStringNotEmpty($this->indexName);
        Assert::nullOrMinLength($this->indexName, 3);
        Assert::nullOrMaxLength($this->indexName, 255);
        Assert::nullOrRegex($this->indexName, '/^[a-zA-Z0-9_.\-]+$/');
    }
}
