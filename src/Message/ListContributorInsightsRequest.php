<?php

declare(strict_types=1);

namespace Imper86\DynamoDBClient\Message;

use InvalidArgumentException;
use Webmozart\Assert\Assert;

final readonly class ListContributorInsightsRequest
{
    /**
     * @param null|positive-int $maxResults the maximum number of summaries to return, at most 100
     * @param null|non-empty-string $nextToken the `NextToken` of the previous page
     * @param null|non-empty-string $tableName the name or ARN of the table to list the summaries of
     * @throws InvalidArgumentException
     */
    public function __construct(
        public ?int $maxResults = null,
        public ?string $nextToken = null,
        public ?string $tableName = null,
    ) {
        Assert::nullOrRange($this->maxResults, 1, 100);
        Assert::nullOrStringNotEmpty($this->nextToken);
        Assert::nullOrStringNotEmpty($this->tableName);
        Assert::nullOrMaxLength($this->tableName, 1024);
    }
}
