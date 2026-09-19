<?php

declare(strict_types=1);

namespace OoAws\DynamoDBClient\ValueObject;

use Webmozart\Assert\Assert;

/**
 * @extends AbstractSet<string>
 */
final readonly class BlobSet extends AbstractSet
{
    protected static function validateItems(array $items): void
    {
        Assert::allString($items);
    }
}
