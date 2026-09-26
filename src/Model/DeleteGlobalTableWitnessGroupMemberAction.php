<?php

declare(strict_types=1);

namespace Imper86\DynamoDBClient\Model;

use InvalidArgumentException;
use Webmozart\Assert\Assert;

final class DeleteGlobalTableWitnessGroupMemberAction
{
    /**
     * @param non-empty-string $regionName the Region to remove the witness from
     * @throws InvalidArgumentException
     */
    public function __construct(
        public readonly string $regionName,
    ) {
        Assert::stringNotEmpty($this->regionName);
    }
}
