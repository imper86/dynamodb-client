<?php

declare(strict_types=1);

namespace Imper86\DynamoDBClient\ValueObject;

use Webmozart\Assert\Assert;

/**
 * @extends AbstractMap<non-empty-string>
 */
final class NonEmptyStringMap extends AbstractMap
{
    protected static function validate(array $items): void
    {
        Assert::allStringNotEmpty($items);
    }
}
