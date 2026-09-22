<?php

declare(strict_types=1);

namespace Imper86\DynamoDBClient\Message;

use Imper86\DynamoDBClient\Model\ContributorInsightsSummaryList;

final readonly class ListContributorInsightsResponse
{
    /**
     * An account without Contributor Insights answers with an empty `ContributorInsightsSummaries` list,
     * so it defaults to empty instead of to null.
     *
     * @param null|string $nextToken where the next page starts; absent on the last page
     */
    public function __construct(
        public ContributorInsightsSummaryList $contributorInsightsSummaries = new ContributorInsightsSummaryList(),
        public ?string $nextToken = null,
    ) {}
}
