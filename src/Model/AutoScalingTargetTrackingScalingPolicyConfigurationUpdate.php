<?php

declare(strict_types=1);

namespace Imper86\DynamoDBClient\Model;

final class AutoScalingTargetTrackingScalingPolicyConfigurationUpdate
{
    /**
     * @param float $targetValue the utilization, as a percentage, the policy keeps the capacity at
     * @param null|int $scaleInCooldown seconds to wait after one scale in before the next
     * @param null|int $scaleOutCooldown seconds to wait after one scale out before the next
     */
    public function __construct(
        public readonly float $targetValue,
        public readonly ?bool $disableScaleIn = null,
        public readonly ?int $scaleInCooldown = null,
        public readonly ?int $scaleOutCooldown = null,
    ) {}
}
