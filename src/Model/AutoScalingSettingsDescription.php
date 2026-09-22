<?php

declare(strict_types=1);

namespace Imper86\DynamoDBClient\Model;

final readonly class AutoScalingSettingsDescription
{
    public function __construct(
        public ?bool $autoScalingDisabled = null,
        public ?string $autoScalingRoleArn = null,
        public ?int $maximumUnits = null,
        public ?int $minimumUnits = null,
        public ?AutoScalingPolicyDescriptionList $scalingPolicies = null,
    ) {}
}
