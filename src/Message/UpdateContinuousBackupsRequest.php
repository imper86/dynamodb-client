<?php

declare(strict_types=1);

namespace Imper86\DynamoDBClient\Message;

use InvalidArgumentException;
use Imper86\DynamoDBClient\Model\PointInTimeRecoverySpecification;
use Webmozart\Assert\Assert;

final class UpdateContinuousBackupsRequest
{
    /**
     * {@see self::enable()} and {@see self::disable()} build the specification for you.
     *
     * @param non-empty-string $tableName the table name or its ARN
     * @throws InvalidArgumentException
     */
    public function __construct(
        public readonly PointInTimeRecoverySpecification $pointInTimeRecoverySpecification,
        public readonly string $tableName,
    ) {
        Assert::stringNotEmpty($this->tableName);
        Assert::maxLength($this->tableName, 1024);
    }

    /**
     * Turns point in time recovery on, or changes the recovery period of a table that already has it.
     *
     * @param non-empty-string $tableName the table name or its ARN
     * @param null|positive-int $recoveryPeriodInDays how many preceding days the table can be restored to, 1 to
     *                                                35; the service defaults to 35
     * @throws InvalidArgumentException
     */
    public static function enable(string $tableName, ?int $recoveryPeriodInDays = null): self
    {
        return new self(new PointInTimeRecoverySpecification(true, $recoveryPeriodInDays), $tableName);
    }

    /**
     * Turns point in time recovery off.
     *
     * @param non-empty-string $tableName the table name or its ARN
     * @throws InvalidArgumentException
     */
    public static function disable(string $tableName): self
    {
        return new self(new PointInTimeRecoverySpecification(false), $tableName);
    }
}
