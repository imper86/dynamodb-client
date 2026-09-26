<?php

declare(strict_types=1);

namespace Imper86\DynamoDBClient\ValueObject;

use Webmozart\Assert\Assert;

/**
 * A list of strings with no further constraint, for values the service chose rather than the caller.
 *
 * @extends AbstractList<string>
 */
final class StringList extends AbstractList
{
    protected static function validate(array $items): void
    {
        Assert::allString($items);
    }
}
