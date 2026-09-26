<?php

declare(strict_types=1);

namespace Imper86\DynamoDBClient\Message;

use InvalidArgumentException;
use Webmozart\Assert\Assert;

final class ListTagsOfResourceRequest
{
    /**
     * @param non-empty-string $resourceArn the ARN of the table, index or stream whose tags to list
     * @param null|non-empty-string $nextToken the `NextToken` of the previous page
     * @throws InvalidArgumentException
     */
    public function __construct(
        public readonly string $resourceArn,
        public readonly ?string $nextToken = null,
    ) {
        Assert::stringNotEmpty($this->resourceArn);
        Assert::maxLength($this->resourceArn, 1283);
        Assert::nullOrStringNotEmpty($this->nextToken);
    }
}
