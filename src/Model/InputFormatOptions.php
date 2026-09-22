<?php

declare(strict_types=1);

namespace Imper86\DynamoDBClient\Model;

use Imper86\DynamoDBClient\ValueObject\NonEmptyStringList;
use InvalidArgumentException;

use function array_values;

final readonly class InputFormatOptions
{
    public function __construct(
        public ?CsvOptions $csv = null,
    ) {}

    /**
     * @param null|non-empty-string $delimiter
     * @param null|array<non-empty-string> $headerList
     * @throws InvalidArgumentException
     */
    public static function csv(?string $delimiter = null, ?array $headerList = null): self
    {
        return new self(new CsvOptions(
            delimiter: $delimiter,
            headerList: null === $headerList ? null : new NonEmptyStringList(array_values($headerList)),
        ));
    }
}
