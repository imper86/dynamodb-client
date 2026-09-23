<?php

declare(strict_types=1);

namespace Imper86\DynamoDBClient\Model;

use InvalidArgumentException;
use Webmozart\Assert\Assert;

final readonly class PointInTimeRecoverySpecification
{
    /**
     * @param null|positive-int $recoveryPeriodInDays how many preceding days the table can be restored to, 1 to
     *                                                35; the service defaults to 35
     * @throws InvalidArgumentException
     */
    public function __construct(
        public bool $pointInTimeRecoveryEnabled,
        public ?int $recoveryPeriodInDays = null,
    ) {
        Assert::nullOrRange($this->recoveryPeriodInDays, 1, 35);
    }
}
