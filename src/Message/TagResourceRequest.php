<?php

declare(strict_types=1);

namespace Imper86\DynamoDBClient\Message;

use InvalidArgumentException;
use Imper86\DynamoDBClient\Model\TagList;
use Webmozart\Assert\Assert;

final readonly class TagResourceRequest
{
    /**
     * @param non-empty-string $resourceArn the ARN of the table, index or stream to tag
     * @param TagList $tags a tag whose key the resource already carries gets the new value
     * @throws InvalidArgumentException
     */
    public function __construct(
        public string $resourceArn,
        public TagList $tags,
    ) {
        Assert::stringNotEmpty($this->resourceArn);
        Assert::maxLength($this->resourceArn, 1283);
    }
}
