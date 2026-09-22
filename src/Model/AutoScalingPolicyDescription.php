<?php

declare(strict_types=1);

namespace Imper86\DynamoDBClient\Model;

final readonly class AutoScalingPolicyDescription
{
    public function __construct(
        public ?string $policyName = null,
        public ?AutoScalingTargetTrackingScalingPolicyConfigurationDescription $targetTrackingScalingPolicyConfiguration = null,
    ) {}
}
