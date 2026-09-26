<?php

declare(strict_types=1);

namespace Imper86\DynamoDBClient\Model;

final class AutoScalingTargetTrackingScalingPolicyConfigurationDescription
{
    public function __construct(
        public readonly ?bool $disableScaleIn = null,
        public readonly ?int $scaleInCooldown = null,
        public readonly ?int $scaleOutCooldown = null,
        public readonly ?float $targetValue = null,
    ) {}
}
