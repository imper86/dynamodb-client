<?php

declare(strict_types=1);

namespace Imper86\DynamoDBClient\Message;

use Imper86\DynamoDBClient\Model\TagList;

final readonly class ListTagsOfResourceResponse
{
    /**
     * A resource without tags answers with an empty `Tags` list, so it defaults to empty instead of to
     * null.
     *
     * @param null|string $nextToken where the next page starts; absent on the last page
     */
    public function __construct(
        public TagList $tags = new TagList(),
        public ?string $nextToken = null,
    ) {}
}
