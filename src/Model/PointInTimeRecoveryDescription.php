<?php

declare(strict_types=1);

namespace Imper86\DynamoDBClient\Model;

use DateTimeImmutable;

final readonly class PointInTimeRecoveryDescription
{
    /**
     * @param null|DateTimeImmutable $latestRestorableDateTime typically five minutes before the current time
     * @param null|int $recoveryPeriodInDays how many preceding days the table can be restored to, 1 to 35
     */
    public function __construct(
        public ?DateTimeImmutable $earliestRestorableDateTime = null,
        public ?DateTimeImmutable $latestRestorableDateTime = null,
        public ?PointInTimeRecoveryStatus $pointInTimeRecoveryStatus = null,
        public ?int $recoveryPeriodInDays = null,
    ) {}
}
