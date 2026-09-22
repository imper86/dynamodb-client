<?php

declare(strict_types=1);

namespace Imper86\DynamoDBClient\Model;

use Imper86\DynamoDBClient\ValueObject\AbstractObjectList;

/**
 * @extends AbstractObjectList<ContributorInsightsSummary>
 */
final readonly class ContributorInsightsSummaryList extends AbstractObjectList
{
    /**
     * @return class-string<ContributorInsightsSummary>
     */
    public static function itemType(): string
    {
        return ContributorInsightsSummary::class;
    }
}
