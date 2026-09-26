<?php

declare(strict_types=1);

namespace Imper86\DynamoDBClient\Model;

use InvalidArgumentException;
use Webmozart\Assert\Assert;

final class CreateGlobalTableWitnessGroupMemberAction
{
    /**
     * @param non-empty-string $regionName the Region to add a witness in
     * @throws InvalidArgumentException
     */
    public function __construct(
        public readonly string $regionName,
    ) {
        Assert::stringNotEmpty($this->regionName);
    }
}
