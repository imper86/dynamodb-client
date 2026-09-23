<?php

declare(strict_types=1);

namespace Imper86\DynamoDBClient\Message;

use InvalidArgumentException;
use Imper86\DynamoDBClient\ValueObject\NonEmptyStringList;
use Webmozart\Assert\Assert;

final readonly class UntagResourceRequest
{
    /**
     * @param non-empty-string $resourceArn the ARN of the table, index or stream to remove the tags from
     * @param NonEmptyStringList $tagKeys the keys of the tags to remove; a key the resource does not carry is
     *                                    ignored
     * @throws InvalidArgumentException
     */
    public function __construct(
        public string $resourceArn,
        public NonEmptyStringList $tagKeys,
    ) {
        Assert::stringNotEmpty($this->resourceArn);
        Assert::maxLength($this->resourceArn, 1283);
        Assert::allMaxLength($this->tagKeys->toArray(), 128);
    }
}
