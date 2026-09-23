<?php

declare(strict_types=1);

namespace Imper86\DynamoDBClient\Model;

use InvalidArgumentException;
use Webmozart\Assert\Assert;

final readonly class AutoScalingSettingsUpdate
{
    /**
     * @param null|non-empty-string $autoScalingRoleArn
     * @param null|positive-int $maximumUnits
     * @param null|positive-int $minimumUnits
     * @throws InvalidArgumentException
     */
    public function __construct(
        public ?bool $autoScalingDisabled = null,
        public ?string $autoScalingRoleArn = null,
        public ?int $maximumUnits = null,
        public ?int $minimumUnits = null,
        public ?AutoScalingPolicyUpdate $scalingPolicyUpdate = null,
    ) {
        Assert::nullOrStringNotEmpty($this->autoScalingRoleArn);
        Assert::nullOrMaxLength($this->autoScalingRoleArn, 1600);
        Assert::nullOrPositiveInteger($this->maximumUnits);
        Assert::nullOrPositiveInteger($this->minimumUnits);
    }
}
