<?php

declare(strict_types=1);

namespace Imper86\DynamoDBClient\Message;

use InvalidArgumentException;
use Webmozart\Assert\Assert;

final readonly class ListImportsRequest
{
    /**
     * @param null|non-empty-string $nextToken the `NextToken` of the previous page
     * @param null|positive-int $pageSize the maximum number of imports to return, at most 25
     * @param null|non-empty-string $tableArn the ARN of the table the imports were made into
     * @throws InvalidArgumentException
     */
    public function __construct(
        public ?string $nextToken = null,
        public ?int $pageSize = null,
        public ?string $tableArn = null,
    ) {
        Assert::nullOrStringNotEmpty($this->nextToken);
        Assert::nullOrMinLength($this->nextToken, 112);
        Assert::nullOrMaxLength($this->nextToken, 1024);
        Assert::nullOrRegex($this->nextToken, '/^([0-9a-f]{16})+$/');
        Assert::nullOrRange($this->pageSize, 1, 25);
        Assert::nullOrStringNotEmpty($this->tableArn);
        Assert::nullOrMaxLength($this->tableArn, 1024);
    }
}
