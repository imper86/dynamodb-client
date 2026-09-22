<?php

declare(strict_types=1);

namespace Imper86\DynamoDBClient\Message;

use DateTimeImmutable;
use Imper86\DynamoDBClient\Model\ContributorInsightsMode;
use Imper86\DynamoDBClient\Model\ContributorInsightsStatus;
use Imper86\DynamoDBClient\Model\FailureException;
use Imper86\DynamoDBClient\ValueObject\StringList;

final readonly class DescribeContributorInsightsResponse
{
    /**
     * The rule list is left null rather than empty because a table that never had Contributor Insights
     * enabled has no rules to report; `FailureException` only comes back once a change has failed.
     *
     * @param null|string $indexName the global secondary index described, when the request named one
     * @param null|DateTimeImmutable $lastUpdateDateTime the last time the status changed
     */
    public function __construct(
        public ?ContributorInsightsMode $contributorInsightsMode = null,
        public ?StringList $contributorInsightsRuleList = null,
        public ?ContributorInsightsStatus $contributorInsightsStatus = null,
        public ?FailureException $failureException = null,
        public ?string $indexName = null,
        public ?DateTimeImmutable $lastUpdateDateTime = null,
        public ?string $tableName = null,
    ) {}
}
