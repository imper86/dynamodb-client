<?php

declare(strict_types=1);

namespace Imper86\DynamoDBClient\Message;

use InvalidArgumentException;
use Imper86\DynamoDBClient\Model\ContributorInsightsAction;
use Imper86\DynamoDBClient\Model\ContributorInsightsMode;
use Webmozart\Assert\Assert;

final class UpdateContributorInsightsRequest
{
    /**
     * @param non-empty-string $tableName the table name or its ARN
     * @param null|non-empty-string $indexName the global secondary index to update instead of the table
     * @throws InvalidArgumentException
     */
    public function __construct(
        public readonly ContributorInsightsAction $contributorInsightsAction,
        public readonly string $tableName,
        public readonly ?ContributorInsightsMode $contributorInsightsMode = null,
        public readonly ?string $indexName = null,
    ) {
        Assert::stringNotEmpty($this->tableName);
        Assert::maxLength($this->tableName, 1024);
        Assert::nullOrStringNotEmpty($this->indexName);
        Assert::nullOrMinLength($this->indexName, 3);
        Assert::nullOrMaxLength($this->indexName, 255);
        Assert::nullOrRegex($this->indexName, '/^[a-zA-Z0-9_.\-]+$/');
    }

    /**
     * @param non-empty-string $tableName the table name or its ARN
     * @param null|non-empty-string $indexName the global secondary index to enable instead of the table
     * @throws InvalidArgumentException
     */
    public static function enable(
        string $tableName,
        ?ContributorInsightsMode $contributorInsightsMode = null,
        ?string $indexName = null,
    ): self {
        return new self(
            contributorInsightsAction: ContributorInsightsAction::ENABLE,
            tableName: $tableName,
            contributorInsightsMode: $contributorInsightsMode,
            indexName: $indexName,
        );
    }

    /**
     * @param non-empty-string $tableName the table name or its ARN
     * @param null|non-empty-string $indexName the global secondary index to disable instead of the table
     * @throws InvalidArgumentException
     */
    public static function disable(string $tableName, ?string $indexName = null): self
    {
        return new self(
            contributorInsightsAction: ContributorInsightsAction::DISABLE,
            tableName: $tableName,
            indexName: $indexName,
        );
    }
}
