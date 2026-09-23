<?php

declare(strict_types=1);

namespace Imper86\DynamoDBClient\Message;

use Imper86\DynamoDBClient\Model\ContributorInsightsMode;
use Imper86\DynamoDBClient\Model\ContributorInsightsStatus;

final readonly class UpdateContributorInsightsResponse
{
    /**
     * @param null|ContributorInsightsStatus $contributorInsightsStatus typically `ENABLING` or `DISABLING` while
     *                                                                  the change takes effect
     * @param null|string $indexName the global secondary index updated, when the request named one
     */
    public function __construct(
        public ?ContributorInsightsMode $contributorInsightsMode = null,
        public ?ContributorInsightsStatus $contributorInsightsStatus = null,
        public ?string $indexName = null,
        public ?string $tableName = null,
    ) {}
}
