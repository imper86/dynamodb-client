<?php

declare(strict_types=1);

namespace Imper86\DynamoDBClient\Model;

use Imper86\DynamoDBClient\ValueObject\NonEmptyStringList;
use InvalidArgumentException;
use Webmozart\Assert\Assert;

final readonly class CsvOptions
{
    /**
     * @param null|non-empty-string $delimiter a single character: comma, semicolon, colon, pipe, tab or space;
     *                                         DynamoDB defaults to a comma
     * @param null|NonEmptyStringList $headerList the column names, when the files have no header line of
     *                                            their own
     * @throws InvalidArgumentException
     */
    public function __construct(
        public ?string $delimiter = null,
        public ?NonEmptyStringList $headerList = null,
    ) {
        Assert::nullOrRegex($this->delimiter, '/^[,;:|\t ]$/');
        Assert::nullOrMinCount($this->headerList, 1);
        Assert::nullOrMaxCount($this->headerList, 255);

        if ($this->headerList instanceof NonEmptyStringList) {
            Assert::allMaxLength($this->headerList->toArray(), 65536);
            Assert::allRegex($this->headerList->toArray(), '/^[\x20-\x21\x23-\x2B\x2D-\x7E]*$/');
        }
    }
}
