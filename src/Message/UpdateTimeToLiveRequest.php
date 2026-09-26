<?php

declare(strict_types=1);

namespace Imper86\DynamoDBClient\Message;

use Imper86\DynamoDBClient\Model\TimeToLiveSpecification;
use InvalidArgumentException;
use Webmozart\Assert\Assert;

final class UpdateTimeToLiveRequest
{
    /**
     * {@see self::enable()} and {@see self::disable()} build the specification for you.
     *
     * @param non-empty-string $tableName the table name or its ARN
     * @throws InvalidArgumentException
     */
    public function __construct(
        public readonly string $tableName,
        public readonly TimeToLiveSpecification $timeToLiveSpecification,
    ) {
        Assert::stringNotEmpty($this->tableName);
        Assert::maxLength($this->tableName, 1024);
    }

    /**
     * Turns Time to Live on, expiring each item at the epoch seconds its `$attributeName` holds.
     *
     * @param non-empty-string $tableName the table name or its ARN
     * @param non-empty-string $attributeName
     * @throws InvalidArgumentException
     */
    public static function enable(string $tableName, string $attributeName): self
    {
        return new self($tableName, new TimeToLiveSpecification($attributeName, true));
    }

    /**
     * Turns Time to Live off.
     *
     * @param non-empty-string $tableName the table name or its ARN
     * @param non-empty-string $attributeName the attribute Time to Live is currently enabled on
     * @throws InvalidArgumentException
     */
    public static function disable(string $tableName, string $attributeName): self
    {
        return new self($tableName, new TimeToLiveSpecification($attributeName, false));
    }
}
