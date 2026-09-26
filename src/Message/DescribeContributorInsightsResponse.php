<?php

declare(strict_types=1);

namespace Imper86\DynamoDBClient\Message;

use DateTimeImmutable;
use Imper86\DynamoDBClient\Model\ContributorInsightsMode;
use Imper86\DynamoDBClient\Model\ContributorInsightsStatus;
use Imper86\DynamoDBClient\Model\FailureException;
use Imper86\DynamoDBClient\ValueObject\StringList;

final class DescribeContributorInsightsResponse
{
    /**
     * The rule list is left null rather than empty because a table that never had Contributor Insights
     * enabled has no rules to report; `FailureException` only comes back once a change has failed.
     *
     * @param null|string $indexName the global secondary index described, when the request named one
     * @param null|DateTimeImmutable $lastUpdateDateTime the last time the status changed
     */
    public function __construct(
        public readonly ?ContributorInsightsMode $contributorInsightsMode = null,
        public readonly ?StringList $contributorInsightsRuleList = null,
        public readonly ?ContributorInsightsStatus $contributorInsightsStatus = null,
        public readonly ?FailureException $failureException = null,
        public readonly ?string $indexName = null,
        public readonly ?DateTimeImmutable $lastUpdateDateTime = null,
        public readonly ?string $tableName = null,
    ) {}
}
