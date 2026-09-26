<?php

declare(strict_types=1);

namespace Imper86\DynamoDBClient\Model;

final class AutoScalingPolicyDescription
{
    public function __construct(
        public readonly ?string $policyName = null,
        public readonly ?AutoScalingTargetTrackingScalingPolicyConfigurationDescription $targetTrackingScalingPolicyConfiguration = null,
    ) {}
}
