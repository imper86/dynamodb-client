<?php

declare(strict_types=1);

namespace Imper86\DynamoDBClient\Model;

final readonly class ContributorInsightsSummary
{
    /**
     * @param null|string $indexName the global secondary index summarized; absent for the table itself
     */
    public function __construct(
        public ?ContributorInsightsMode $contributorInsightsMode = null,
        public ?ContributorInsightsStatus $contributorInsightsStatus = null,
        public ?string $indexName = null,
        public ?string $tableName = null,
    ) {}
}
