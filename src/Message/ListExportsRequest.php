<?php

declare(strict_types=1);

namespace Imper86\DynamoDBClient\Message;

use InvalidArgumentException;
use Webmozart\Assert\Assert;

final readonly class ListExportsRequest
{
    /**
     * @param null|positive-int $maxResults the maximum number of exports to return, at most 25
     * @param null|non-empty-string $nextToken the `NextToken` of the previous page
     * @param null|non-empty-string $tableArn the ARN of the table to list the exports of
     * @throws InvalidArgumentException
     */
    public function __construct(
        public ?int $maxResults = null,
        public ?string $nextToken = null,
        public ?string $tableArn = null,
    ) {
        Assert::nullOrRange($this->maxResults, 1, 25);
        Assert::nullOrStringNotEmpty($this->nextToken);
        Assert::nullOrStringNotEmpty($this->tableArn);
        Assert::nullOrMaxLength($this->tableArn, 1024);
    }
}
