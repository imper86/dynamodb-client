<?php

declare(strict_types=1);

namespace Imper86\DynamoDBClient\Model;

final class AutoScalingSettingsDescription
{
    public function __construct(
        public readonly ?bool $autoScalingDisabled = null,
        public readonly ?string $autoScalingRoleArn = null,
        public readonly ?int $maximumUnits = null,
        public readonly ?int $minimumUnits = null,
        public readonly ?AutoScalingPolicyDescriptionList $scalingPolicies = null,
    ) {}
}
