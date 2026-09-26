<?php

declare(strict_types=1);

namespace Imper86\DynamoDBClient\ValueObject;

use Webmozart\Assert\Assert;

use function is_float;
use function is_int;

/**
 * A list of JSON numbers. JSON does not tell a double from an integer, so a whole number such as
 * `1` decodes to an int and is kept as one.
 *
 * @extends AbstractList<float|int>
 */
final class DoubleList extends AbstractList
{
    protected static function validate(array $items): void
    {
        foreach ($items as $item) {
            Assert::true(is_float($item) || is_int($item), 'Expected a list of numbers.');
        }
    }
}
