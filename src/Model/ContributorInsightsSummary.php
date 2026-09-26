<?php

declare(strict_types=1);

namespace Imper86\DynamoDBClient\Model;

final class ContributorInsightsSummary
{
    /**
     * @param null|string $indexName the global secondary index summarized; absent for the table itself
     */
    public function __construct(
        public readonly ?ContributorInsightsMode $contributorInsightsMode = null,
        public readonly ?ContributorInsightsStatus $contributorInsightsStatus = null,
        public readonly ?string $indexName = null,
        public readonly ?string $tableName = null,
    ) {}
}
