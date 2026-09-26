<?php

declare(strict_types=1);

namespace Imper86\DynamoDBClient\Model;

use InvalidArgumentException;
use Webmozart\Assert\Assert;

final class AutoScalingPolicyUpdate
{
    /**
     * @param null|non-empty-string $policyName
     * @throws InvalidArgumentException
     */
    public function __construct(
        public readonly AutoScalingTargetTrackingScalingPolicyConfigurationUpdate $targetTrackingScalingPolicyConfiguration,
        public readonly ?string $policyName = null,
    ) {
        Assert::nullOrStringNotEmpty($this->policyName);
        Assert::nullOrMaxLength($this->policyName, 256);
    }

    /**
     * @param float $targetValue the utilization, as a percentage, the policy keeps the capacity at
     * @param null|int $scaleInCooldown seconds to wait after one scale in before the next
     * @param null|int $scaleOutCooldown seconds to wait after one scale out before the next
     * @param null|non-empty-string $policyName
     * @throws InvalidArgumentException
     */
    public static function targetTracking(
        float $targetValue,
        ?bool $disableScaleIn = null,
        ?int $scaleInCooldown = null,
        ?int $scaleOutCooldown = null,
        ?string $policyName = null,
    ): self {
        return new self(
            targetTrackingScalingPolicyConfiguration: new AutoScalingTargetTrackingScalingPolicyConfigurationUpdate(
                targetValue: $targetValue,
                disableScaleIn: $disableScaleIn,
                scaleInCooldown: $scaleInCooldown,
                scaleOutCooldown: $scaleOutCooldown,
            ),
            policyName: $policyName,
        );
    }
}
