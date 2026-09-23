<?php

declare(strict_types=1);

namespace Imper86\DynamoDBClient\Model;

final readonly class AutoScalingTargetTrackingScalingPolicyConfigurationUpdate
{
    /**
     * @param float $targetValue the utilization, as a percentage, the policy keeps the capacity at
     * @param null|int $scaleInCooldown seconds to wait after one scale in before the next
     * @param null|int $scaleOutCooldown seconds to wait after one scale out before the next
     */
    public function __construct(
        public float $targetValue,
        public ?bool $disableScaleIn = null,
        public ?int $scaleInCooldown = null,
        public ?int $scaleOutCooldown = null,
    ) {}
}
