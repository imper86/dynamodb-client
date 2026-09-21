<?php

declare(strict_types=1);

namespace Imper86\DynamoDBClient\ValueObject;

use Webmozart\Assert\Assert;

/**
 * @extends AbstractSet<numeric-string>
 */
final readonly class NumberSet extends AbstractSet
{
    protected static function validateItems(array $items): void
    {
        Assert::allNumeric($items);
    }
}
