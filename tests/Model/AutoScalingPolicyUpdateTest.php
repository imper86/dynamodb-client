<?php

declare(strict_types=1);

namespace Imper86\DynamoDBClientTests\Model;

use InvalidArgumentException;
use Imper86\DynamoDBClient\Model\AutoScalingPolicyUpdate;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

use function str_repeat;

/**
 * @internal
 */
#[CoversClass(AutoScalingPolicyUpdate::class)]
final class AutoScalingPolicyUpdateTest extends TestCase
{
    /**
     * @throws InvalidArgumentException
     */
    public function testBuildsATargetTrackingPolicy(): void
    {
        $update = AutoScalingPolicyUpdate::targetTracking(60.5, false, 60, 30, 'ReadPolicy');

        $configuration = $update->targetTrackingScalingPolicyConfiguration;

        self::assertSame(60.5, $configuration->targetValue);
        self::assertFalse($configuration->disableScaleIn);
        self::assertSame(60, $configuration->scaleInCooldown);
        self::assertSame(30, $configuration->scaleOutCooldown);
        self::assertSame('ReadPolicy', $update->policyName);
    }

    /**
     * @throws InvalidArgumentException
     */
    public function testLeavesTheOptionalSettingsNull(): void
    {
        $update = AutoScalingPolicyUpdate::targetTracking(70);

        $configuration = $update->targetTrackingScalingPolicyConfiguration;

        self::assertSame(70.0, $configuration->targetValue);
        self::assertNull($configuration->disableScaleIn);
        self::assertNull($configuration->scaleInCooldown);
        self::assertNull($configuration->scaleOutCooldown);
        self::assertNull($update->policyName);
    }

    /**
     * @throws InvalidArgumentException
     */
    public function testRejectsAPolicyNameLongerThanTheServiceAllows(): void
    {
        $this->expectException(InvalidArgumentException::class);

        AutoScalingPolicyUpdate::targetTracking(70, policyName: str_repeat('a', 257));
    }
}
