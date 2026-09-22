<?php

declare(strict_types=1);

namespace Imper86\DynamoDBClient\Model;

use Imper86\DynamoDBClient\ValueObject\AbstractObjectList;

/**
 * @extends AbstractObjectList<AutoScalingPolicyDescription>
 */
final readonly class AutoScalingPolicyDescriptionList extends AbstractObjectList
{
    /**
     * @return class-string<AutoScalingPolicyDescription>
     */
    public static function itemType(): string
    {
        return AutoScalingPolicyDescription::class;
    }
}
