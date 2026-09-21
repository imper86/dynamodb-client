<?php

declare(strict_types=1);

namespace Imper86\DynamoDBClient\ValueObject;

use Webmozart\Assert\Assert;

/**
 * @extends AbstractSet<string>
 */
final readonly class StringSet extends AbstractSet
{
    protected static function validateItems(array $items): void
    {
        Assert::allString($items);
    }
}
