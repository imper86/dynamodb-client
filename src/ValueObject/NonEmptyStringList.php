<?php

declare(strict_types=1);

namespace OoAws\DynamoDBClient\ValueObject;

use Webmozart\Assert\Assert;

/**
 * @extends AbstractList<non-empty-string>
 */
final readonly class NonEmptyStringList extends AbstractList
{
    protected static function validate(array $items): void
    {
        Assert::allStringNotEmpty($items);
    }
}
