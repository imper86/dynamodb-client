<?php

declare(strict_types=1);

namespace Imper86\DynamoDBClient\ValueObject;

use Webmozart\Assert\Assert;

/**
 * @extends AbstractList<non-empty-string>
 */
final class NonEmptyStringList extends AbstractList
{
    protected static function validate(array $items): void
    {
        Assert::allStringNotEmpty($items);
    }
}
