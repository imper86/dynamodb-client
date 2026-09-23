<?php

declare(strict_types=1);

namespace Imper86\DynamoDBClient\Message;

use InvalidArgumentException;
use Imper86\DynamoDBClient\Model\Tag;
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

    /**
     * Takes the tags as a key-to-value map. PHP turns a numeric key such as `'2024'` into an integer, so every
     * key goes back to a string before it becomes a {@see Tag}.
     *
     * @param non-empty-string $resourceArn the ARN of the table, index or stream to tag
     * @param array<int|non-empty-string, string> $tags
     * @throws InvalidArgumentException
     */
    public static function tags(string $resourceArn, array $tags): self
    {
        $list = [];

        foreach ($tags as $key => $value) {
            $list[] = new Tag((string) $key, $value);
        }

        return new self($resourceArn, new TagList($list));
    }
}
