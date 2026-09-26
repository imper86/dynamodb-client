<?php

declare(strict_types=1);

namespace Imper86\DynamoDBClient\Model;

use DateTimeImmutable;

final class PointInTimeRecoveryDescription
{
    /**
     * @param null|DateTimeImmutable $latestRestorableDateTime typically five minutes before the current time
     * @param null|int $recoveryPeriodInDays how many preceding days the table can be restored to, 1 to 35
     */
    public function __construct(
        public readonly ?DateTimeImmutable $earliestRestorableDateTime = null,
        public readonly ?DateTimeImmutable $latestRestorableDateTime = null,
        public readonly ?PointInTimeRecoveryStatus $pointInTimeRecoveryStatus = null,
        public readonly ?int $recoveryPeriodInDays = null,
    ) {}
}
