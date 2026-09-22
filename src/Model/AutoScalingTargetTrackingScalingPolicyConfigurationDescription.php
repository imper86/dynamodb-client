<?php

declare(strict_types=1);

namespace Imper86\DynamoDBClient\Model;

final readonly class AutoScalingTargetTrackingScalingPolicyConfigurationDescription
{
    public function __construct(
        public ?bool $disableScaleIn = null,
        public ?int $scaleInCooldown = null,
        public ?int $scaleOutCooldown = null,
        public ?float $targetValue = null,
    ) {}
}
