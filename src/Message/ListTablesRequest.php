<?php

declare(strict_types=1);

namespace Imper86\DynamoDBClient\Message;

use InvalidArgumentException;
use Webmozart\Assert\Assert;

final readonly class ListTablesRequest
{
    /**
     * @param null|non-empty-string $exclusiveStartTableName the `LastEvaluatedTableName` of the previous page
     * @param null|positive-int $limit the maximum number of table names to return, at most 100; DynamoDB
     *                                 defaults to 100
     * @throws InvalidArgumentException
     */
    public function __construct(
        public ?string $exclusiveStartTableName = null,
        public ?int $limit = null,
    ) {
        Assert::nullOrStringNotEmpty($this->exclusiveStartTableName);
        Assert::nullOrMinLength($this->exclusiveStartTableName, 3);
        Assert::nullOrMaxLength($this->exclusiveStartTableName, 255);
        Assert::nullOrRegex($this->exclusiveStartTableName, '/^[a-zA-Z0-9_.\-]+$/');
        Assert::nullOrRange($this->limit, 1, 100);
    }
}
