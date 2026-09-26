<?php

declare(strict_types=1);

namespace Imper86\DynamoDBClient\Message;

use Imper86\DynamoDBClient\Model\ContributorInsightsMode;
use Imper86\DynamoDBClient\Model\ContributorInsightsStatus;

final class UpdateContributorInsightsResponse
{
    /**
     * @param null|ContributorInsightsStatus $contributorInsightsStatus typically `ENABLING` or `DISABLING` while
     *                                                                  the change takes effect
     * @param null|string $indexName the global secondary index updated, when the request named one
     */
    public function __construct(
        public readonly ?ContributorInsightsMode $contributorInsightsMode = null,
        public readonly ?ContributorInsightsStatus $contributorInsightsStatus = null,
        public readonly ?string $indexName = null,
        public readonly ?string $tableName = null,
    ) {}
}
