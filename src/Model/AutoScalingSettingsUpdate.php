<?php

declare(strict_types=1);

namespace Imper86\DynamoDBClient\Model;

use InvalidArgumentException;
use Webmozart\Assert\Assert;

final class AutoScalingSettingsUpdate
{
    /**
     * @param null|non-empty-string $autoScalingRoleArn
     * @param null|positive-int $maximumUnits
     * @param null|positive-int $minimumUnits
     * @throws InvalidArgumentException
     */
    public function __construct(
        public readonly ?bool $autoScalingDisabled = null,
        public readonly ?string $autoScalingRoleArn = null,
        public readonly ?int $maximumUnits = null,
        public readonly ?int $minimumUnits = null,
        public readonly ?AutoScalingPolicyUpdate $scalingPolicyUpdate = null,
    ) {
        Assert::nullOrStringNotEmpty($this->autoScalingRoleArn);
        Assert::nullOrMaxLength($this->autoScalingRoleArn, 1600);
        Assert::nullOrPositiveInteger($this->maximumUnits);
        Assert::nullOrPositiveInteger($this->minimumUnits);
    }
}
